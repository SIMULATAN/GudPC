<?php
	class Migrator
	{
		# const in a class because shitty ass php doesn't allow final variables outside of classes lmao
		const CURRENT_VERSION = 5;

		public static function init($dbconn) {
			pg_exec($dbconn, "
				CREATE TABLE IF NOT EXISTS migrations
				(
					version INTEGER PRIMARY KEY,
					date   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
				);
			");
		}

		/**
		 * @return bool true if migration ran (no init required), false if an init is required
		 */
		public static function doMigration($dbconn): bool {
			$dbVersion = pg_query($dbconn, "SELECT COALESCE(max(version), 0) AS version FROM migrations");
			if (!$dbVersion) {
				echo "DB Version Query failed!\n";
				return true;
			}
			$dbVersion = pg_fetch_row($dbVersion)[0];

			if ($dbVersion > self::CURRENT_VERSION) {
				echo "DB Version is above current version!\n";
				return true;
			} elseif ($dbVersion == self::CURRENT_VERSION) {
				echo "DB version is the same as the current version, yay :)\n";
				return true;
			} elseif ($dbVersion == 0) {
				echo "DB version is 0, an init will be required!\n";
				return false;
			}

			$migrationFileDirectory = "sql/migrations";
			$migrationFiles = array_diff(scandir($migrationFileDirectory), array('.', '..'));
			if (sizeof($migrationFiles) == 0) {
				echo "No migrations found\n";
				return true;
			}

			pg_exec($dbconn, "BEGIN");
			foreach ($migrationFiles as $migrationFile) {
				$matches = preg_match("/^(\\d+)_(.*)\\.sql$/", $migrationFile, $groups);
				if (!$matches) {
					echo "Migration file $migrationFile doesn't match the migration file pattern!\n";
					return true;
				}

				$fileVersion = $groups[1];
				if ($dbVersion < $fileVersion) {
					echo "Migrating $migrationFile...\n";
					$result = pg_exec($dbconn, file_get_contents("$migrationFileDirectory/{$migrationFile}"));
					if (!$result) {
						echo "Couldn't run migration $migrationFile!\n";
						pg_exec($dbconn, "ROLLBACK");
						return true;
					}
				}
			}
			// fuck you php why can't i use `self::CURRENT_VERSION` in string interpolation wtf???
			$currentVersion = self::CURRENT_VERSION;
			pg_exec($dbconn, "INSERT INTO migrations VALUES ($currentVersion)");
			pg_exec($dbconn, "COMMIT");
			echo "Migrations ran successfully!\n";

			return true;
		}
	}

	# I despise PHP for this like what the fuck
	require_once "../../config/config.php";
	global $config;

	global $dbconn;
	$dbconn = pg_connect($config->db_connection_string);

	Migrator::init($dbconn);
	if (Migrator::doMigration($dbconn)) {
		echo "Migrator ran successfully or errored i dont fucking know, anyway i'm exiting now...\n";
		exit();
	}

	// create tables
	$create_tables = file_get_contents("sql/create.sql");
	// begin transaction
	pg_query($dbconn, "BEGIN");
	$result = pg_query($dbconn, $create_tables);
	// check if it worked
	if ($result) {
		echo "Database initialized - inserting version....\n";
		$newVersion = Migrator::CURRENT_VERSION;
		pg_exec($dbconn, "INSERT INTO migrations VALUES ($newVersion)");
		echo "DB version inserted :) comitting now...\n";
		pg_exec($dbconn, "COMMIT");
		echo "Commit done les gooo\n";
	} else {
		echo "\nDatabase initialization FAILED - make sure this truly is the first ever run!\n";
		pg_exec($dbconn, "ROLLBACK");
	}
