CREATE TABLE permission (
    name VARCHAR(255) PRIMARY KEY NOT NULL
);

CREATE TABLE user_permission (
    user_id INT NOT NULL REFERENCES users(id),
    permission VARCHAR(255) NOT NULL REFERENCES permission(name),
    PRIMARY KEY (user_id, permission)
);

INSERT INTO permission (name) VALUES ('product_manager');
