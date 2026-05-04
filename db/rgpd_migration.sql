-- RGPD: añadir registro de consentimiento al formulario de contacto.
-- Ejecuta este script una sola vez sobre la base de datos de producción.

ALTER TABLE contact_submissions
    ADD COLUMN consent_at DATETIME NULL DEFAULT NULL AFTER source_page,
    ADD COLUMN consent_ip VARCHAR(45) NULL DEFAULT NULL AFTER consent_at;

-- Para envíos antiguos sin registro explícito, marcamos la fecha de envío
-- como consentimiento implícito (el formulario ya estaba enviado antes del cambio).
UPDATE contact_submissions
   SET consent_at = submitted_at
 WHERE consent_at IS NULL;
