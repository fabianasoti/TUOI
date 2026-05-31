-- Traducciones EN de la carta_items
-- Generado: 2026-05-27
-- Aplica nombre_en, descripcion_en, ingredientes_en, subcategoria_en a todos los ítems.
-- Idempotente: se puede re-ejecutar sin duplicar nada (UPDATE por id).

START TRANSACTION;

-- ╔════════════════════════════════════════════════════════════════╗
-- ║ BEBIDAS                                                        ║
-- ╚════════════════════════════════════════════════════════════════╝

-- Cafés
UPDATE carta_items SET subcategoria_en='Coffees', nombre_en='ESPRESSO',          descripcion_en='Intense and pure. Quick energy with deep roasted notes.'                  WHERE id=75;
UPDATE carta_items SET subcategoria_en='Coffees', nombre_en='CORTADO',           descripcion_en='Balanced and smooth. Bold coffee with a light touch of milk.'             WHERE id=76;
UPDATE carta_items SET subcategoria_en='Coffees', nombre_en='BOMBÓN',            descripcion_en='Creamy and sweet. Coffee with condensed milk — a small treat.'           WHERE id=77;
UPDATE carta_items SET subcategoria_en='Coffees', nombre_en='WHITE COFFEE',      descripcion_en='Smooth and comforting. A balanced blend for any time of day.'           WHERE id=78;
UPDATE carta_items SET subcategoria_en='Coffees', nombre_en='ORZO COFFEE (BARLEY)', descripcion_en='Caffeine-free, natural and light. Toasted flavour that takes care of you.' WHERE id=79;
UPDATE carta_items SET subcategoria_en='Coffees', nombre_en='AMERICANO',         descripcion_en='Light and smooth. Steady energy to keep you going for hours.'             WHERE id=80;
UPDATE carta_items SET subcategoria_en='Coffees', nombre_en='CAPPUCCINO',        descripcion_en='Creamy and airy. Bold coffee topped with soft, light foam.'              WHERE id=81;
UPDATE carta_items SET subcategoria_en='Coffees', nombre_en='Latte',             descripcion_en='Smooth and balanced. More milk, less intensity.'                         WHERE id=82;
UPDATE carta_items SET subcategoria_en='Coffees', nombre_en='Flat White',        descripcion_en='Silky and bold. Concentrated coffee with a fine texture.'                WHERE id=83;
UPDATE carta_items SET subcategoria_en='Coffees', nombre_en='LATTE MACCHIATO',   descripcion_en='Smooth and creamy. Silky milk with an intense espresso touch.'           WHERE id=84;
UPDATE carta_items SET subcategoria_en='Coffees', nombre_en='CARAMEL LATTE',     descripcion_en='Sweet and comforting. Smooth coffee with a creamy caramel flavour.'      WHERE id=85;

-- Lattes de especialidad
UPDATE carta_items SET subcategoria_en='Specialty lattes', nombre_en='CHAI LATTE',     descripcion_en='Spiced and warm. Gentle energy with a natural blend of spices.'      WHERE id=86;
UPDATE carta_items SET subcategoria_en='Specialty lattes', nombre_en='MATCHA LATTE',   descripcion_en='Clean, antioxidant energy. Sustained focus without spikes.'         WHERE id=87;
UPDATE carta_items SET subcategoria_en='Specialty lattes', nombre_en='Turmeric latte', descripcion_en='Spiced and comforting. Creamy blend with warm, gentle notes.'        WHERE id=88;
UPDATE carta_items SET subcategoria_en='Specialty lattes', nombre_en='PINK LATTE',     descripcion_en='Light and floral. Antioxidant and eye-catching — surprises and nourishes.' WHERE id=89;

-- Chocolate drinks
UPDATE carta_items SET subcategoria_en='CHOCOLATE DRINKS', nombre_en='CLASSIC',     descripcion_en='Peruvian cacao with dates. Natural sweetness and essential antioxidants.' WHERE id=90;
UPDATE carta_items SET subcategoria_en='CHOCOLATE DRINKS', nombre_en='CHOCO LATTE', descripcion_en='Bold and creamy. Smooth chocolate blended with silky milk.'             WHERE id=91;
UPDATE carta_items SET subcategoria_en='CHOCOLATE DRINKS', nombre_en='FOCUS',       descripcion_en='Ceremonial cacao, lion''s mane, cordyceps, ginger, cardamom.'          WHERE id=92;
UPDATE carta_items SET subcategoria_en='CHOCOLATE DRINKS', nombre_en='RELAX',       descripcion_en='Ceremonial cacao, ashwagandha, reishi, cinnamon, clove, star anise, nutmeg.' WHERE id=93;

-- Tés e infusiones
UPDATE carta_items SET subcategoria_en='Teas & infusions', nombre_en='Black tea / English Breakfast', descripcion_en='Bold and stimulating. Classic energy to start the day.'          WHERE id=94;
UPDATE carta_items SET subcategoria_en='Teas & infusions', nombre_en='Green / red / white tea',       descripcion_en='Natural antioxidants. Balance and lightness in every cup.'      WHERE id=95;
UPDATE carta_items SET subcategoria_en='Teas & infusions', nombre_en='Ginger and lemon',              descripcion_en='Revitalising and digestive. Activates body and natural system.' WHERE id=96;
UPDATE carta_items SET subcategoria_en='Teas & infusions', nombre_en='RED BERRIES',                   descripcion_en='Soft and fruity. Light sweetness with an antioxidant touch.'    WHERE id=97;
UPDATE carta_items SET subcategoria_en='Teas & infusions', nombre_en='Rooibos orange and lemon',      descripcion_en='Caffeine-free, citrusy and smooth. Perfect for any moment.'    WHERE id=98;
UPDATE carta_items SET subcategoria_en='Teas & infusions', nombre_en='Chamomile / pennyroyal',        descripcion_en='Relaxing and digestive. Natural calm for body and mind.'       WHERE id=99;
UPDATE carta_items SET subcategoria_en='Teas & infusions', nombre_en='PU-ERH',                        descripcion_en='Deep and earthy. Bold infusion with warm, enveloping notes.'    WHERE id=100;
UPDATE carta_items SET subcategoria_en='Teas & infusions', nombre_en='Green tea masala chai',         descripcion_en='Fresh and spiced. Gentle energy with natural warmth.'          WHERE id=101;

-- Matchas
UPDATE carta_items SET subcategoria_en='MATCHAS', nombre_en='MATCHA LATTE',         descripcion_en='Smooth and green. Creamy matcha with silky, balanced milk.'        WHERE id=102;
UPDATE carta_items SET subcategoria_en='MATCHAS', nombre_en='ICED MATCHA COCO',     descripcion_en='Tropical and refreshing. Iced matcha with smooth, light coconut milk.' WHERE id=103;
UPDATE carta_items SET subcategoria_en='MATCHAS', nombre_en='ICED MATCHA MANGO',    descripcion_en='Exotic and vibrant. Iced matcha with a sweet, juicy mango touch.'  WHERE id=104;
UPDATE carta_items SET subcategoria_en='MATCHAS', nombre_en='ICED MATCHA STRAWBERRY', descripcion_en='Fresh and fruity. Iced matcha with sweet strawberry notes.'      WHERE id=105;

-- Limonadas
UPDATE carta_items SET subcategoria_en='LEMONADES', nombre_en='pineapple, red berries, classic, strawberry, mango', descripcion_en='Refreshing and fruity. Natural flavours with a sweet citrus touch.' WHERE id=112;
UPDATE carta_items SET subcategoria_en='LEMONADES', nombre_en='COCONUT AND AVOCADO',  descripcion_en='Creamy and tropical. A smooth, refreshing blend with an exotic touch.' WHERE id=113;

-- Smoothies naturales
UPDATE carta_items SET subcategoria_en='NATURAL SMOOTHIES', nombre_en='BALANCE',  descripcion_en='Bold and stimulating. Classic energy to start the day.'        WHERE id=106;
UPDATE carta_items SET subcategoria_en='NATURAL SMOOTHIES', nombre_en='ANTIOX',   descripcion_en='Natural antioxidants. Balance and lightness in every cup.'      WHERE id=107;
UPDATE carta_items SET subcategoria_en='NATURAL SMOOTHIES', nombre_en='FOCUS',    descripcion_en='Revitalising and digestive. Activates body and natural system.' WHERE id=108;
UPDATE carta_items SET subcategoria_en='NATURAL SMOOTHIES', nombre_en='ENERGY',   descripcion_en='Soft and fruity. Light sweetness with an antioxidant touch.'    WHERE id=109;
UPDATE carta_items SET subcategoria_en='NATURAL SMOOTHIES', nombre_en='PITAYA',   descripcion_en='Smooth and tropical. Refreshing with a sweet, exotic touch.'    WHERE id=110;
UPDATE carta_items SET subcategoria_en='NATURAL SMOOTHIES', nombre_en='AÇAI',     descripcion_en='Bold and fruity. Creamy texture with a fresh, energising flavour.' WHERE id=111;

-- Mejora tu bebida
UPDATE carta_items SET subcategoria_en='upgrade your drink', nombre_en='Coconut, almond or oat milk' WHERE id=114;
UPDATE carta_items SET subcategoria_en='upgrade your drink', nombre_en='HONEY OR AGAVE SYRUP'        WHERE id=115;
UPDATE carta_items SET subcategoria_en='upgrade your drink', nombre_en='SUPERFOOD'                   WHERE id=116;
UPDATE carta_items SET subcategoria_en='upgrade your drink', nombre_en='ICE'                         WHERE id=117;

-- ╔════════════════════════════════════════════════════════════════╗
-- ║ DESAYUNOS                                                      ║
-- ╚════════════════════════════════════════════════════════════════╝

-- Tostadas
UPDATE carta_items SET subcategoria_en='Toasts', nombre_en='Tomato and EVOO',                                       descripcion_en='Mediterranean classic. Fresh, light and full of natural flavour.' WHERE id=1;
UPDATE carta_items SET subcategoria_en='Toasts', nombre_en='Butter and artisan jam',                                descripcion_en='Sweet and smooth. Traditional breakfast with a natural artisan touch.' WHERE id=2;
UPDATE carta_items SET subcategoria_en='Toasts', nombre_en='Tomato, olive oil and avocado',                         descripcion_en='Creamy and fresh. Healthy fats and balanced energy.' WHERE id=3;
UPDATE carta_items SET subcategoria_en='Toasts', nombre_en='Iberian ham and tomato',                                descripcion_en='Bold and authentic. Deep flavour with a Mediterranean touch.' WHERE id=4;
UPDATE carta_items SET subcategoria_en='Toasts', nombre_en='Gran Reserva serrano ham and tomato',                   descripcion_en='Classic and tasty. The perfect balance of tradition and freshness.' WHERE id=5;
UPDATE carta_items SET subcategoria_en='Toasts', nombre_en='Hummus, rocket, cherry tomato and crispy onion',        descripcion_en='Plant-based and packed with texture. Light, nourishing and full of flavour.' WHERE id=6;
UPDATE carta_items SET subcategoria_en='Toasts', nombre_en='Salmon, avocado cream and cherry tomato',               descripcion_en='Fresh and nourishing. Protein and healthy fats in balance.' WHERE id=7;
UPDATE carta_items SET subcategoria_en='Toasts', nombre_en='Peanut butter, banana and agave',                       descripcion_en='Energising and sweet. Perfect for a natural strong start.' WHERE id=8;
UPDATE carta_items SET subcategoria_en='Toasts', nombre_en='Scrambled egg, avocado and cherry tomato',              descripcion_en='Protein-rich and satisfying. Full energy to start the day.' WHERE id=9;

-- Bowls
UPDATE carta_items SET subcategoria_en='Bowls', nombre_en='Seasonal fruit salad',                               descripcion_en='Fresh and natural. Vitamins and light energy to start the day.' WHERE id=16;
UPDATE carta_items SET subcategoria_en='Bowls', nombre_en='Homemade granola with yoghurt, fruit and agave',     descripcion_en='Crunchy and balanced. Natural energy with a healthy sweet touch.' WHERE id=17;
UPDATE carta_items SET subcategoria_en='Bowls', nombre_en='Açaí bowl with peanut, mango and granola',           descripcion_en='Refreshing and energising. Antioxidants and balanced tropical flavour.' WHERE id=18;

-- Wraps
UPDATE carta_items SET subcategoria_en='Wraps', nombre_en='breakfast wrap', descripcion_en='Free-range scrambled eggs, smoked bacon, cheddar cheese and young sprouts. Served with a fresh side salad.' WHERE id=19;
UPDATE carta_items SET subcategoria_en='Wraps', nombre_en='CHICKEN AND CHEESE', descripcion_en='Chicken, emmental cheese, young sprouts, chickpea hummus, cherry tomatoes and cream cheese. Served with a fresh side salad.' WHERE id=20;

-- Bocadillos (sandwiches)
UPDATE carta_items SET subcategoria_en='Sandwiches', nombre_en='SCRAMBLED EGGS AND BACON' WHERE id=21;
UPDATE carta_items SET subcategoria_en='Sandwiches', nombre_en='HUMMUS AND SUN-DRIED TOMATO' WHERE id=22;
UPDATE carta_items SET subcategoria_en='Sandwiches', nombre_en='CHICKEN AND CHEESE' WHERE id=23;
UPDATE carta_items SET subcategoria_en='Sandwiches', nombre_en='Creamy tuna' WHERE id=24;
UPDATE carta_items SET subcategoria_en='Sandwiches', nombre_en='Spanish potato omelette' WHERE id=25;

-- Extras
UPDATE carta_items SET subcategoria_en='Extras', nombre_en='FRESH CHEESE',         descripcion_en='Light and smooth. Adds freshness and natural protein.'      WHERE id=10;
UPDATE carta_items SET subcategoria_en='Extras', nombre_en='SALMON',               descripcion_en='Rich in omega 3. Clean and nourishing flavour.'             WHERE id=11;
UPDATE carta_items SET subcategoria_en='Extras', nombre_en='Scrambled egg',        descripcion_en='Complete protein. Soft texture and satisfying.'             WHERE id=12;
UPDATE carta_items SET subcategoria_en='Extras', nombre_en='AVOCADO',              descripcion_en='Healthy fats. Creamy, nourishing and balanced.'             WHERE id=13;
UPDATE carta_items SET subcategoria_en='Extras', nombre_en='TURKEY',               descripcion_en='Light and protein-rich. Ideal for milder options.'           WHERE id=14;
UPDATE carta_items SET subcategoria_en='Extras', nombre_en='GRAN RESERVA SERRANO HAM', descripcion_en='Bold and cured. Traditional flavour with character.'    WHERE id=15;
UPDATE carta_items SET subcategoria_en='Extras', nombre_en='GLUTEN-FREE BREAD',    descripcion_en='Light and easy to digest. A suitable alternative without losing flavour.' WHERE id=26;

-- Desayuno tradicional
UPDATE carta_items SET subcategoria_en='Traditional breakfast', nombre_en='Coffee or tea + toast or pastry', descripcion_en='Classic and simple. A quick breakfast for any time of day.' WHERE id=27;

-- ╔════════════════════════════════════════════════════════════════╗
-- ║ MENU-BRUNCH                                                    ║
-- ╚════════════════════════════════════════════════════════════════╝

-- 1. Elige un principal
UPDATE carta_items SET subcategoria_en='1. Pick a main', nombre_en='CLASSIC EGGS AND BACON',                    descripcion_en='Free-range scrambled eggs, smoked bacon, confit cherry tomatoes and sourdough toast.' WHERE id=28;
UPDATE carta_items SET subcategoria_en='1. Pick a main', nombre_en='SCRAMBLED EGGS AND BACON sandwich',         descripcion_en='Free-range scrambled eggs, butter, smoked bacon and extra virgin olive oil.' WHERE id=29;
UPDATE carta_items SET subcategoria_en='1. Pick a main', nombre_en='HUMMUS AND SUN-DRIED TOMATO sandwich',      descripcion_en='Homemade hummus, sun-dried tomato, rocket, crispy onion.' WHERE id=30;
UPDATE carta_items SET subcategoria_en='1. Pick a main', nombre_en='CHICKEN AND CHEESE sandwich',               descripcion_en='Roast chicken, edam cheese and caramelised onion.' WHERE id=31;
UPDATE carta_items SET subcategoria_en='1. Pick a main', nombre_en='CREAMY TUNA sandwich',                      descripcion_en='Tuna, fresh tomato, pickle and TUOI sauce.' WHERE id=32;
UPDATE carta_items SET subcategoria_en='1. Pick a main', nombre_en='DELICIOUS focaccia',                        descripcion_en='Italian pistachio pesto, Italian mortadella and fresh mozzarella.' WHERE id=33;
UPDATE carta_items SET subcategoria_en='1. Pick a main', nombre_en='GRAN RESERVA HAM toast',                    descripcion_en='Homemade hummus, cherry tomato, rocket, crispy onion.' WHERE id=35;
UPDATE carta_items SET subcategoria_en='1. Pick a main', nombre_en='veggie toast',                              descripcion_en='Homemade hummus, cherry tomato, rocket, crispy onion and extra virgin olive oil.' WHERE id=34;
UPDATE carta_items SET subcategoria_en='1. Pick a main', nombre_en='Salmon and avocado toast',                  descripcion_en='Sliced avocado, smoked salmon, cheese and extra virgin olive oil.' WHERE id=36;
UPDATE carta_items SET subcategoria_en='1. Pick a main', nombre_en='THAI Bowl',                                 descripcion_en='Mixed green sprouts, roast chicken, caramelised onion and TUOI sauce.' WHERE id=38;
UPDATE carta_items SET subcategoria_en='1. Pick a main', nombre_en='avocado and egg toast',                     descripcion_en='Avocado, scrambled egg, cherry tomato and extra virgin olive oil.' WHERE id=37;

-- 2. Selecciona tu bebida
UPDATE carta_items SET subcategoria_en='2. Choose your drink', nombre_en='COFFEES',         descripcion_en='- ESPRESSO\n- AMERICANO\n- BATCH BREW\n- FLAT WHITE\n- LATTE\n- ICED LATTE\n- CAPPUCCINO' WHERE id=39;
UPDATE carta_items SET subcategoria_en='2. Choose your drink', nombre_en='Juices - Soft drinks', descripcion_en='- FRESH ORANGE JUICE\n- COLD BREW\n- MINERAL WATER\n- COCA COLA\n- AQUARIUS ORANGE\n- FUZE TEA\n- TONIC' WHERE id=40;

-- 3. Añade tu postre
UPDATE carta_items SET subcategoria_en='3. Add your dessert', nombre_en='Sweet moment', descripcion_en='- CROISSANT\n- BROWNIE\n- CHOCOLATE COOKIE\n- YOGHURT WITH HOMEMADE GRANOLA\n- CINNAMON ROLL\n- BAKED CHEESECAKE\n- MANGO AND PINEAPPLE' WHERE id=41;

-- ╔════════════════════════════════════════════════════════════════╗
-- ║ MENU-LUNCH                                                     ║
-- ╚════════════════════════════════════════════════════════════════╝

UPDATE carta_items SET subcategoria_en='Full menu', nombre_en='INCLUDES:', descripcion_en='- Chef''s starter\n- Balanced main: choose from two carb options and two protein options\n- 33cl bottle of water\n- Dessert or coffee' WHERE id=73;
UPDATE carta_items SET subcategoria_en='Half menu', nombre_en='INCLUDES:', descripcion_en='- Balanced main: choose from two carb options and two protein options' WHERE id=74;

-- ╔════════════════════════════════════════════════════════════════╗
-- ║ MOMENTO-DULCE                                                  ║
-- ╚════════════════════════════════════════════════════════════════╝

-- Croissant
UPDATE carta_items SET subcategoria_en='Croissant', nombre_en='BUTTER',     descripcion_en='Crispy and flaky. Classic flavour with quality butter.'        WHERE id=43;
UPDATE carta_items SET subcategoria_en='Croissant', nombre_en='chocolate',  descripcion_en='Golden and creamy. Melting chocolate in every bite.'           WHERE id=44;
UPDATE carta_items SET subcategoria_en='Croissant', nombre_en='pistachio',  descripcion_en='Smooth and aromatic. Creamy filling with a delicate pistachio flavour.' WHERE id=45;
UPDATE carta_items SET subcategoria_en='Croissant', nombre_en='multigrain', descripcion_en='Light and nourishing. Crispy texture with a mix of grains.'    WHERE id=46;

-- Cookies
UPDATE carta_items SET subcategoria_en='Cookies', nombre_en='american double chocolate',     descripcion_en='Bold and chewy. Double chocolate for an indulgent treat.' WHERE id=51;
UPDATE carta_items SET subcategoria_en='Cookies', nombre_en='oats, seeds and dried fruits',  descripcion_en='Crunchy and nourishing. Natural energy with selected ingredients.' WHERE id=52;

-- Mini
UPDATE carta_items SET subcategoria_en='mini', nombre_en='Mini cinnamon roll',     descripcion_en='Soft and spiced. Gentle sweetness with a warm aroma.'        WHERE id=47;
UPDATE carta_items SET subcategoria_en='mini', nombre_en='Mini croissant',         descripcion_en='Small and light. The perfect classic for any moment.'        WHERE id=48;
UPDATE carta_items SET subcategoria_en='mini', nombre_en='Mini cream croissant',   descripcion_en='Smooth and creamy. Sweet filling in a light format.'         WHERE id=49;
UPDATE carta_items SET subcategoria_en='mini', nombre_en='Mini chocolate croissant', descripcion_en='Small and indulgent. Smooth chocolate in every bite.'      WHERE id=50;

-- Plum cake
UPDATE carta_items SET subcategoria_en='PLUM CAKE', nombre_en='CARROT',       descripcion_en='Soft and moist. Natural sweetness with a gentle spiced touch.' WHERE id=63;
UPDATE carta_items SET subcategoria_en='PLUM CAKE', nombre_en='Banana bread', descripcion_en='Juicy and comforting. Natural flavour with balanced sweetness.' WHERE id=64;
UPDATE carta_items SET subcategoria_en='PLUM CAKE', nombre_en='LEMON',        descripcion_en='Light and fresh. A citrus touch that wakes up the senses.'    WHERE id=65;

-- ╔════════════════════════════════════════════════════════════════╗
-- ║ TOQUE-SALADO                                                   ║
-- ╚════════════════════════════════════════════════════════════════╝

-- Bowls equilibrados
UPDATE carta_items SET subcategoria_en='BALANCED BOWLS', nombre_en='bangkok',   descripcion_en='Rice, roast chicken, rocket, cherry tomato, avocado, mozzarella, pesto sauce.' WHERE id=69;
UPDATE carta_items SET subcategoria_en='BALANCED BOWLS', nombre_en='MELBOURNE', descripcion_en='Quinoa, spinach, spiced chickpeas, feta cheese, tomato, pepper in yoghurt sauce.' WHERE id=70;
UPDATE carta_items SET subcategoria_en='BALANCED BOWLS', nombre_en='LIMA',      descripcion_en='Quinoa, prawns, cabbage, mango, cherry tomato, lettuce, pickled onion, peanut sauce.' WHERE id=71;

-- Ensaladas
UPDATE carta_items SET subcategoria_en='SALADS', nombre_en='MILANO', descripcion_en='Lettuce, toasted bread, crispy bacon, cherry tomato, roast chicken, parmesan shavings, caesar dressing.' WHERE id=66;
UPDATE carta_items SET subcategoria_en='SALADS', nombre_en='MADRID', descripcion_en='Lettuce, tomato, goat cheese, raisins, walnuts and tuna.' WHERE id=67;
UPDATE carta_items SET subcategoria_en='SALADS', nombre_en='ROMA',   descripcion_en='Rocket, cherry tomato, olives, mozzarella and pesto.' WHERE id=68;

-- Empanadillas
UPDATE carta_items SET subcategoria_en='EMPANADAS', nombre_en='RATATOUILLE',           descripcion_en='Homemade filling with traditional Mediterranean flavour.'         WHERE id=53;
UPDATE carta_items SET subcategoria_en='EMPANADAS', nombre_en='SPINACH AND GOAT CHEESE', descripcion_en='Smooth and balanced. Plant-based with a creamy, slightly tangy touch.' WHERE id=54;
UPDATE carta_items SET subcategoria_en='EMPANADAS', nombre_en='BEEF',                  descripcion_en='Tasty and juicy. Spiced meat with a bold, comforting flavour.'   WHERE id=55;
UPDATE carta_items SET subcategoria_en='EMPANADAS', nombre_en='CHICKEN',               descripcion_en='Light and tasty. Smooth filling with a balanced homemade touch.' WHERE id=56;

-- Mini bocadillos
UPDATE carta_items SET subcategoria_en='MINI SANDWICHES', nombre_en='Creamy tuna',                                       descripcion_en='Smooth and creamy. Balanced flavour with a fresh touch.' WHERE id=57;
UPDATE carta_items SET subcategoria_en='MINI SANDWICHES', nombre_en='Salmon and cream cheese',                           descripcion_en='Fresh and delicate. Balance between creaminess and sea flavour.' WHERE id=58;
UPDATE carta_items SET subcategoria_en='MINI SANDWICHES', nombre_en='Turkey and cheese',                                 descripcion_en='Light and smooth. A balanced option for any moment.' WHERE id=59;
UPDATE carta_items SET subcategoria_en='MINI SANDWICHES', nombre_en='Spanish potato omelette',                           descripcion_en='Classic and juicy. Traditional flavour in a practical format.' WHERE id=60;
UPDATE carta_items SET subcategoria_en='MINI SANDWICHES', nombre_en='Serrano ham and brie',                              descripcion_en='Bold and creamy. Perfect contrast between cured and smooth.' WHERE id=61;
UPDATE carta_items SET subcategoria_en='MINI SANDWICHES', nombre_en='Mortadella, mozzarella and pistachio pesto focaccia', descripcion_en='Aromatic and tasty. Italian inspiration with a gourmet touch.' WHERE id=62;

COMMIT;

-- Verificación rápida:
-- SELECT COUNT(*) AS total, SUM(nombre_en IS NOT NULL AND nombre_en <> '') AS traducidos FROM carta_items;
