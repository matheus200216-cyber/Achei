ALTER TABLE usuarios
    ADD COLUMN provisionado_por INT NULL DEFAULT NULL AFTER polo_id,
    ADD INDEX idx_usuarios_provisionado_por (provisionado_por);