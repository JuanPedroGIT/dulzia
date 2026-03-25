<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create service and service_example tables with seed data';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE service (
            id VARCHAR(100) NOT NULL,
            name VARCHAR(255) NOT NULL,
            emoji VARCHAR(20) NOT NULL,
            description TEXT NOT NULL,
            features JSON NOT NULL,
            category VARCHAR(50) NOT NULL,
            sort_order INTEGER NOT NULL DEFAULT 0,
            is_active BOOLEAN NOT NULL DEFAULT TRUE,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE TABLE service_example (
            id VARCHAR(36) NOT NULL,
            service_id VARCHAR(100) NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            image_url VARCHAR(500) NOT NULL,
            sort_order INTEGER NOT NULL DEFAULT 0,
            PRIMARY KEY(id),
            CONSTRAINT fk_service_example_service FOREIGN KEY (service_id) REFERENCES service(id) ON DELETE CASCADE
        )');

        $this->addSql('CREATE INDEX idx_service_example_service ON service_example (service_id)');

        // ── Seed services ───────────────────────────────────────────────
        $services = [
            ['carrito-hot-dog', 'Carrito de Hot Dog', '🌭', 'Un clásico reinventado. Nuestro carrito de perritos calientes es el hit de cualquier celebración, con panes artesanales y toppings para todos los gustos.', '["Pan artesanal","Variedad de salsas","Opciones vegetarianas","Montaje incluido"]', 'food', 1],
            ['candy-bar', 'Candy Bar', '🍬', 'Una explosión de color y dulzura. Decoramos tu espacio con una barra de dulces personalizada que combina perfectamente con la estética de tu evento.', '["Diseño personalizado","Variedad de golosinas","Bandejas y recipientes decorativos","Etiquetado personalizado"]', 'food', 2],
            ['fuente-chocolate', 'Fuente de Chocolate', '🍫', 'La estrella de los postres. Una fuente de chocolate con fondue, acompañada de frutas, bizcochos y marshmallows para una experiencia deliciosa.', '["Chocolate belga de alta calidad","Variedad de acompañamientos","Servicio continuo","Opción chocolate blanco y negro"]', 'food', 3],
            ['palomitero', 'Palomitero', '🍿', 'El olor y el sabor que todos conocen. Nuestra máquina de palomitas de maíz llena cualquier espacio de aroma y alegría en el momento exacto.', '["Palomitas recién hechas","Sabores variados","Bolsas personalizadas","Operación continua"]', 'food', 4],
            ['algodon-azucar', 'Algodón de Azúcar', '🩷', 'Nubes de azúcar que hacen volar la imaginación. El algodón de azúcar artesanal es una experiencia sensorial que encanta a pequeños y mayores.', '["Colores personalizados","Sabores variados","Hecho al momento","Presentaciones especiales"]', 'food', 5],
            ['carteles-bienvenida', 'Carteles de Bienvenida', '🪧', 'El primer detalle que verán tus invitados. Creamos carteles únicos y personalizados que dan la bienvenida con estilo a cualquier celebración.', '["Diseño a medida","Caligrafía artesanal","Múltiples formatos","Materiales premium"]', 'decoration', 6],
            ['picnic-tipis', 'Picnic & Tipis', '⛺', 'Crea un ambiente mágico y acogedor. Nuestros sets de picnic con tipis son perfectos para celebraciones al aire libre y momentos únicos.', '["Tipis y cojines decorativos","Mesas bajas","Luces cálidas","Montaje y desmontaje"]', 'decoration', 7],
            ['photocall', 'Photocall & Decoraciones', '📸', 'El rincón perfecto para los mejores recuerdos. Diseñamos photocalls y decoraciones temáticas que se convierten en el corazón visual de tu evento.', '["Diseño personalizado","Props y accesorios","Múltiples estilos","Montaje incluido"]', 'decoration', 8],
            ['regalos-personalizados', 'Regalos Personalizados', '🎁', 'El detalle que hace la diferencia. Creamos regalos y detalles personalizados para tus invitados, desde packaging hasta productos únicos con tu nombre.', '["Diseño a medida","Packaging premium","Opciones variadas","Pedidos desde 20 unidades"]', 'decoration', 9],
            ['mini-ferias', 'Mini Ferias', '🎡', 'La diversión de la feria en tu evento. Juegos tradicionales, actividades para niños y adultos que crean un ambiente festivo y lleno de alegría.', '["Juegos clásicos de feria","Para todas las edades","Animadores incluidos","Premios y regalos"]', 'experience', 10],
            ['glitter-bar', 'Glitter Bar', '✨', 'Brillar nunca fue tan fácil. Nuestro Glitter Bar aplica brillo cosmético de forma profesional para que tus invitados luzcan increíbles en cada foto.', '["Glitter cosmético seguro","Artista especializada","Diseños personalizados","Materiales hipoalergénicos"]', 'experience', 11],
        ];

        foreach ($services as [$id, $name, $emoji, $description, $features, $category, $order]) {
            $this->addSql(
                'INSERT INTO service (id, name, emoji, description, features, category, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)',
                [$id, $name, $emoji, $description, $features, $category, $order]
            );
        }

        // ── Seed examples ────────────────────────────────────────────────
        $examples = [
            // carrito-hot-dog
            ['carrito-hot-dog', 'Boda en jardín con decoración floral', 'Carrito decorado con guirnaldas de flores naturales y luces cálidas para una boda íntima al aire libre. Los invitados disfrutaron de hot dogs gourmet con ingredientes artesanales.', 'https://picsum.photos/seed/hotdog-1/800/600', 1],
            ['carrito-hot-dog', 'Cumpleaños temático años 80', 'Montaje retro con luces de neón y decoración pop para un cumpleaños sorpresa. El carrito fue el centro de atención de la noche con sus toppings creativos.', 'https://picsum.photos/seed/hotdog-2/800/600', 2],
            ['carrito-hot-dog', 'Evento corporativo en terraza', 'Servicio elegante para una empresa tecnológica en su fiesta de verano. Presentación minimalista con mantelería blanca y branding personalizado en el carrito.', 'https://picsum.photos/seed/hotdog-3/800/600', 3],
            ['carrito-hot-dog', 'Comunión con decoración pastel', 'Carrito en tonos pastel con guirnaldas y globos para una comunión de 80 comensales. Ofrecimos versiones mini para los más pequeños y opciones vegetarianas para los adultos.', 'https://picsum.photos/seed/hotdog-4/800/600', 4],
            // candy-bar
            ['candy-bar', 'Candy bar romántico en tonos rosas', 'Mesa de dulces en tonos blanco y rosa palo para una boda íntima. Macarons, trufas, nubes y caramelos artesanales presentados en recipientes de cristal con etiquetas caligráficas.', 'https://picsum.photos/seed/candybar-1/800/600', 1],
            ['candy-bar', 'Mesa dulce infantil arcoíris', 'Derroche de color para un cumpleaños infantil de 5 años. Cada sección de la mesa representaba un color del arcoíris con gominolas, piruletas y nubes a juego.', 'https://picsum.photos/seed/candybar-2/800/600', 2],
            ['candy-bar', 'Candy bar rústico con madera y arpillera', 'Estética boho-rústica para una celebración en finca. Cajas de madera, arpillera y flores silvestres como soporte para dulces artesanales y chocolates de autor.', 'https://picsum.photos/seed/candybar-3/800/600', 3],
            ['candy-bar', 'Mesa dorada para boda de lujo', 'Candy bar en dorado y blanco para una boda de 150 invitados. Torre de macarons, fuente de chocolate blanco, bombones belgas y tartas en miniatura.', 'https://picsum.photos/seed/candybar-4/800/600', 4],
            // fuente-chocolate
            ['fuente-chocolate', 'Fuente triple: negro, con leche y blanco', 'Tres fuentes de distintos tamaños con chocolate belga negro, con leche y blanco para una boda de 200 invitados. Mesa de acompañamientos con 15 opciones: frutas, bizcochos, nubes, oreos y galletas.', 'https://picsum.photos/seed/chocolate-1/800/600', 1],
            ['fuente-chocolate', 'Rincón de postres completo', 'Fuente de chocolate como pieza central de una mesa de postres con cake pops, macarons y tartas en miniatura. Decoración con flores naturales para una boda en finca castellana.', 'https://picsum.photos/seed/chocolate-2/800/600', 2],
            ['fuente-chocolate', 'Fuente temática tropical', 'Chocolate negro con acompañamientos tropicales: piña, mango, fresas bañadas y coco. Decoración con hojas de palma y flores de hibisco para una fiesta de verano.', 'https://picsum.photos/seed/chocolate-3/800/600', 3],
            ['fuente-chocolate', 'Fuente infantil con colores', 'Fuente de chocolate blanco teñido en rosa para un cumpleaños de princesas. Acompañamientos en colores: fresas, nubes rosas, piruletas y bizcochos decorados.', 'https://picsum.photos/seed/chocolate-4/800/600', 4],
            // palomitero
            ['palomitero', 'Carrito vintage estilo cine clásico', 'Carrito de palomitas con estética de cine de los años 50 en rojo y crema para una boda temática. Bolsas personalizadas con los nombres de los novios y la fecha del evento.', 'https://picsum.photos/seed/palomitas-1/800/600', 1],
            ['palomitero', 'Palomitero en boda exterior', 'Servicio de palomitas durante el cóctel de una boda en jardín. Ofrecimos cuatro sabores: mantequilla clásica, sal marina, queso cheddar y caramelo salado con bolsas kraft personalizadas.', 'https://picsum.photos/seed/palomitas-2/800/600', 2],
            ['palomitero', 'Puesto colorido para fiesta infantil', 'Máquina de palomitas decorada con globos y colores vivos para un cumpleaños infantil. Palomitas dulces de colores en bolsas a rayas con el nombre del cumpleañero.', 'https://picsum.photos/seed/palomitas-3/800/600', 3],
            ['palomitero', 'Branding corporativo en evento empresa', 'Carrito personalizado con los colores e identidad visual de una empresa tecnológica para su evento anual. Bolsas con logo y claim de la empresa como detalle para los asistentes.', 'https://picsum.photos/seed/palomitas-4/800/600', 4],
            // algodon-azucar
            ['algodon-azucar', 'Algodón unicornio para fiesta temática', 'Algodón de azúcar en degradado rosa-morado con purpurina comestible dorada para una fiesta de unicornios. Servido en conos decorados y con toppings brillantes.', 'https://picsum.photos/seed/algodon-1/800/600', 1],
            ['algodon-azucar', 'Stand vintage con máquina restaurada', 'Máquina de algodón vintage restaurada en blanco roto con dorados para una boda retro. Algodón en color melocotón y blanco a juego con la paleta cromática del evento.', 'https://picsum.photos/seed/algodon-2/800/600', 2],
            ['algodon-azucar', 'Arcoíris de sabores para cumpleaños', 'Siete sabores y colores de algodón de azúcar para una fiesta infantil de arcoíris. Cada color tenía un sabor diferente: fresa, limón, naranja, menta, uva, mora y vainilla.', 'https://picsum.photos/seed/algodon-3/800/600', 3],
            ['algodon-azucar', 'Algodón artístico en forma de flor', 'Algodón de azúcar moldeado artísticamente en forma de flor para la mesa de postres de una boda. Presentación en soporte de madera como si fuera un ramo decorativo comestible.', 'https://picsum.photos/seed/algodon-4/800/600', 4],
            // carteles-bienvenida
            ['carteles-bienvenida', 'Espejo con caligrafía dorada', 'Espejo enmarcado en dorado envejecido con caligrafía artesanal en rotulador de vinilo. El cartel principal de bienvenida para una boda en masía catalana con los nombres de los novios y la fecha.', 'https://picsum.photos/seed/cartel-1/800/600', 1],
            ['carteles-bienvenida', 'Pizarra negra estilo vintage', 'Gran pizarra de madera con letras blancas escritas a mano para una boda rústica. Incluía el programa del día y un mensaje personal de los novios a sus invitados.', 'https://picsum.photos/seed/cartel-2/800/600', 2],
            ['carteles-bienvenida', 'Cartel de madera grabada al láser', 'Tablón de madera maciza con texto grabado en relieve para una comunión. Diseño personalizado con el nombre, la fecha y un versículo. Detalle que los padres conservan como recuerdo.', 'https://picsum.photos/seed/cartel-3/800/600', 3],
            ['carteles-bienvenida', 'Acrílico transparente minimalista', 'Cartel en metacrilato transparente con letras en vinilo negro para un evento corporativo. Estética moderna y limpia que encajó perfectamente con la identidad visual de la empresa.', 'https://picsum.photos/seed/cartel-4/800/600', 4],
            // picnic-tipis
            ['picnic-tipis', 'Picnic romántico de aniversario', 'Setup íntimo para dos con tipi blanco, cojines de terciopelo y mesa baja decorada con flores silvestres y velas. Luz de hora dorada en campo abierto para una sorpresa de aniversario.', 'https://picsum.photos/seed/picnic-1/800/600', 1],
            ['picnic-tipis', 'Cumpleaños infantil en jardín', 'Cuatro tipis coloridos con cojines divertidos y mesas bajas con actividades para un cumpleaños de 8 niños. Guirnaldas de banderines y alfombras de colores completaron el espacio.', 'https://picsum.photos/seed/picnic-2/800/600', 2],
            ['picnic-tipis', 'Tipi doble para boda bohemia', 'Dos tipis de tela blanca unidos con guirnaldas de luz cálida para los novios y sus invitados más cercanos. Macramé, plumas y flores secas como elementos decorativos principales.', 'https://picsum.photos/seed/picnic-3/800/600', 3],
            ['picnic-tipis', 'Picnic bohemio con estampados étnicos', 'Setup para 12 personas con alfombras kilim, cojines de bordado étnico y linternas de hierro forjado. Celebración de graduación en jardín privado con estética viajera.', 'https://picsum.photos/seed/picnic-4/800/600', 4],
            // photocall
            ['photocall', 'Pared floral para boda', 'Photocall de 2×2m con flores artificiales de alta calidad en tonos blancos, rosas y verde menta. Marco de latón dorado con el nombre de los novios para una boda en hotel de cinco estrellas.', 'https://picsum.photos/seed/photocall-1/800/600', 1],
            ['photocall', 'Letras neón sobre fondo negro', 'Photocall con letra luminosa de neón "LOVE" sobre panel negro para una boda nocturna. El efecto lumínico creó fotografías espectaculares que se convirtieron en el recuerdo favorito de los invitados.', 'https://picsum.photos/seed/photocall-2/800/600', 2],
            ['photocall', 'Arco de globos multicolor', 'Arco de globos en tonos pastel de 3m de altura para un cumpleaños de 30 años. Globos de distintos tamaños y texturas (mate, metalizado y transparente) con estrellitas doradas flotantes.', 'https://picsum.photos/seed/photocall-3/800/600', 3],
            ['photocall', 'Marco dorado vintage con cortina', 'Marco dorado envejecido de 1.8×1.8m con cortina de flecos dorados para una fiesta de Nochevieja. Props temáticos incluidos: sombreros, gafas y carteles con frases del año.', 'https://picsum.photos/seed/photocall-4/800/600', 4],
            // regalos-personalizados
            ['regalos-personalizados', 'Bolsas kraft con caligrafía personalizada', 'Bolsas de papel kraft con el nombre de cada invitado en caligrafía artesanal para una boda de 90 personas. Contenido: tarro de miel local, galleta de mantequilla y nota manuscrita.', 'https://picsum.photos/seed/regalos-1/800/600', 1],
            ['regalos-personalizados', 'Cajas regalo premium para comunión', 'Cajas blancas con tapa y lazo en dorado personalizadas con el nombre del niño y la fecha de la comunión. Contenido seleccionado por los padres: rosquillas, mini botella de agua bendita y estampa.', 'https://picsum.photos/seed/regalos-2/800/600', 2],
            ['regalos-personalizados', 'Tarros de especias para boda rústica', 'Tarros de cristal con especias locales (pimentón, azafrán y orégano) con etiqueta personalizada de la boda. Diseño rústico en papel kraft con rafia que encajó perfectamente con la decoración de la finca.', 'https://picsum.photos/seed/regalos-3/800/600', 3],
            ['regalos-personalizados', 'Pack corporativo con branding', 'Set de merchandising personalizado para evento de empresa: libreta, bolígrafo, posavasos y bolsa de tela con el logo y el claim del evento. Pedido de 150 unidades para entrega en acreditación.', 'https://picsum.photos/seed/regalos-4/800/600', 4],
            // mini-ferias
            ['mini-ferias', 'Feria completa para boda', 'Cinco juegos de feria (tiro al blanco, dardos, argollas, pato de agua y máquina garra) para entretener a los invitados durante el cóctel de una boda de 150 personas en jardín.', 'https://picsum.photos/seed/feria-1/800/600', 1],
            ['mini-ferias', 'Zona infantil para cumpleaños', 'Tres juegos adaptados para niños de 3 a 10 años con animador incluido. Pesca de patos, laberinto de pelotas y circuito de obstáculos para un cumpleaños de 25 niños.', 'https://picsum.photos/seed/feria-2/800/600', 2],
            ['mini-ferias', 'Team building corporativo', 'Mini feria competitiva por equipos para una empresa de 80 trabajadores. Ranking en tiempo real, uniformes de equipo y trofeos personalizados para los ganadores.', 'https://picsum.photos/seed/feria-3/800/600', 3],
            ['mini-ferias', 'Festival de verano en comunidad', 'Instalación de feria en zona común de urbanización para festividad de verano. Ocho juegos para todas las edades con premios y música en directo durante cuatro horas.', 'https://picsum.photos/seed/feria-4/800/600', 4],
            // glitter-bar
            ['glitter-bar', 'Brillo nupcial para la novia', 'Aplicación de glitter sutil en tonos champán y dorado para la novia y sus damas de honor. Diseño de constelaciones en el escote y brazos que captó toda la luz en las fotos de la boda.', 'https://picsum.photos/seed/glitter-1/800/600', 1],
            ['glitter-bar', 'Festival look con colores vivos', 'Glitter bar con paleta completa de colores para un festival de música privado. Diseños llamativos de lunas, estrellas y geométricos en cara, brazos y clavículas.', 'https://picsum.photos/seed/glitter-2/800/600', 2],
            ['glitter-bar', 'Glitter seguro para cumpleaños infantil', 'Brillo cosmético 100% seguro y lavable para niños de 4 a 12 años. Diseños de mariposas, corazones y estrellas que duraron toda la fiesta y se quitaron con agua y jabón.', 'https://picsum.photos/seed/glitter-3/800/600', 3],
            ['glitter-bar', 'Look plateado para gala de empresa', 'Aplicación de glitter plateado y holográfico para los asistentes a una gala corporativa de premios. Diseños elegantes y discretos que complementaban los looks de noche de los asistentes.', 'https://picsum.photos/seed/glitter-4/800/600', 4],
        ];

        foreach ($examples as [$serviceId, $title, $description, $imageUrl, $order]) {
            $id = bin2hex(random_bytes(16));
            $this->addSql(
                'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
                [$id, $serviceId, $title, $description, $imageUrl, $order]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE service_example');
        $this->addSql('DROP TABLE service');
    }
}
