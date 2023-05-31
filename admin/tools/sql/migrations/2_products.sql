CREATE TABLE product (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    cpu_id INTEGER NOT NULL REFERENCES cpu(id),
    gpu_id INTEGER REFERENCES gpu(id),
    ram_id INTEGER NOT NULL REFERENCES ram(id),
    storage_id INTEGER NOT NULL REFERENCES storage(id),
    motherboard_id INTEGER NOT NULL REFERENCES motherboard(id)
);
