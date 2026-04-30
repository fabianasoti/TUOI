-- Actualiza textos de la página de Eventos con la nueva copy
-- (solo afecta filas existentes; si la clave no está en site_content, los defaults
--  de config/content_helper.php ya cubren el caso)

UPDATE site_content SET content_value = 'Eventos con sentido, energía y propósito'
  WHERE content_key = 'ev_hero_h1';

UPDATE site_content SET content_value = 'Experiencias gastronómicas que potencian cada encuentro.'
  WHERE content_key = 'ev_hero_sub';

-- Nueva sección manifiesto (insertar si no existen las claves)
INSERT INTO site_content (content_key, content_value) VALUES
  ('ev_intro_label', 'Nuestra filosofía'),
  ('ev_intro_p1',    'En TUOI llevamos nuestra filosofía de functional coffee & smart food también al mundo de los eventos. Diseñamos experiencias gastronómicas que no solo acompañan, sino que potencian lo que ocurre en cada encuentro: más claridad, mejor energía y una sensación real de bienestar.'),
  ('ev_intro_p2',    'Trabajamos con ingredientes de proximidad y propuestas equilibradas que se adaptan al ritmo y objetivo de cada encuentro. El resultado: comida ligera, sabrosa y funcional, que evita bajones y acompaña el ritmo natural de cada momento.')
ON DUPLICATE KEY UPDATE content_value = VALUES(content_value);

-- Sección de prueba social (testimonio + logos opcionales)
INSERT INTO site_content (content_key, content_value) VALUES
  ('ev_social_label',  'Confían en nosotros'),
  ('ev_social_quote',  'Organizamos un afterwork para 40 personas y la diferencia se notó: la gente conectó, comió bien y nadie sufrió el bajón de media tarde. Volveremos.'),
  ('ev_social_author', 'Marta Soler'),
  ('ev_social_role',   'People & Culture · Innovae')
ON DUPLICATE KEY UPDATE content_value = VALUES(content_value);

UPDATE site_content SET content_value = 'Eventos de networking – Afterworks – Team buildings – Presentaciones – Encuentros corporativos o creativos'
  WHERE content_key = 'ev_marquee_text';

UPDATE site_content SET content_value = 'Menús que se adaptan a tu evento'
  WHERE content_key = 'ev_menus_h2';

UPDATE site_content SET content_value = 'Ofrecemos diferentes formatos que se ajustan al tipo de encuentro y a la experiencia que quieres crear.'
  WHERE content_key = 'ev_menus_intro';

UPDATE site_content SET content_value = 'Coffee break'        WHERE content_key = 'ev_cb_h2';
UPDATE site_content SET content_value = 'Opciones ágiles y equilibradas para pausas que reactivan, favorecen la concentración y mantienen la energía estable.'
  WHERE content_key = 'ev_cb_desc';

UPDATE site_content SET content_value = 'Brunch'              WHERE content_key = 'ev_br_h2';
UPDATE site_content SET content_value = 'Una propuesta más completa y versátil, ideal para encuentros distendidos que combinan trabajo y socialización.'
  WHERE content_key = 'ev_br_desc';

UPDATE site_content SET content_value = 'Tardeo'              WHERE content_key = 'ev_td_h2';
UPDATE site_content SET content_value = 'El formato perfecto para cerrar el día con un ambiente más relajado, sin renunciar a una alimentación cuidada.'
  WHERE content_key = 'ev_td_desc';
