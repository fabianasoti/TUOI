-- Eventos · Simplificación a 2 opciones (Experiencias listas + A tu medida)
-- 1) Borra Tardeo
DELETE FROM eventos_posts WHERE category = 'tardeo';

-- 2) Renombra Coffee Break → eventos-listos
UPDATE eventos_posts SET category = 'eventos-listos' WHERE category = 'coffee-break';

-- 3) Borra Brunch (la nueva opción "a tu medida" no usa posts)
DELETE FROM eventos_posts WHERE category = 'brunch';

-- 4) Limpia textos de categorías eliminadas en site_content
DELETE FROM site_content WHERE content_key IN (
    'ev_cb_label','ev_cb_h2','ev_cb_desc',
    'ev_br_label','ev_br_h2','ev_br_desc',
    'ev_td_label','ev_td_h2','ev_td_desc',
    'ev_cb_label_en','ev_cb_h2_en','ev_cb_desc_en',
    'ev_br_label_en','ev_br_h2_en','ev_br_desc_en',
    'ev_td_label_en','ev_td_h2_en','ev_td_desc_en'
);
