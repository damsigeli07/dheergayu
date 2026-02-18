-- Table for pharmacist product requests (orders) to suppliers
-- Each row = one product line (one product + quantity) per request

CREATE TABLE IF NOT EXISTS product_requests (
    id INT(11) NOT NULL AUTO_INCREMENT,
    product_name VARCHAR(255) NOT NULL,
    quantity INT(11) NOT NULL DEFAULT 1,
    supplier_id INT(11) NOT NULL,
    request_date DATE NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    pharmacist_id INT(11) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_supplier_id (supplier_id),
    KEY idx_pharmacist_id (pharmacist_id),
    KEY idx_request_date (request_date),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
