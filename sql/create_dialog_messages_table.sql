CREATE TABLE IF NOT EXISTS dialog_messages (
    dialog_id VARCHAR(100) NOT NULL,
    user_from_id BIGINT NOT NULL,
    user_to_id BIGINT NOT NULL,
    text VARCHAR(500) NOT NULL,
    created_at timestamp
);



