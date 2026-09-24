<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Contenido real de la web original: textos de servicios y galerías de fotos en R2';
    }

    public function up(Schema $schema): void
    {
        // ── Textos reales (nombre + descripción literal de la web original) ──
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Carrito de Perritos', 'Nuestro carrito de perritos calientes es uno de los servicios más solicitados para cualquier tipo de evento. Es un carrito de comida en directo donde puedes elegir varios productos diferentes. Ideal para recenas en bodas, meriendas en comuniones, comida en cumpleaños, o para cualquier ocasión. ¿A quien no le gusta un perrito caliente o un crepe entre otros productos realizado en directo?', 'carrito-hot-dog']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Candy Bar', 'Nos encargamos de que tengas tu rincón dulce personalizado. Este rincón no es sólo para los niños, una de las frases que más se repite es "tengo pocos niños", estais equivocados porque este ricón es uno de los que más gusta tanto a los niños como a los adultos. Tematizamos, personalizamos y lo hacemos super especial. Cuéntanos tu idea y nosotros lo hacemos realidad, porque lo que más nos gusta es... Crear momentos únicos e inolvidables', 'candy-bar']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Carteles de Bienvenida', 'Los carteles de bienvenida, son un complemento ideal para poner a la entrada del evento, va personalizado y puede ir tematizado. Va todo decorado con globos. En ésta sección os dejamos algunos de los carteles de bienvenida que hemos realizado para diferentes eventos.', 'carteles-bienvenida']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Picnic y Tipis', 'Son servicios cada vez más solicitados por nuestros clientes. Son servicios para fiestas de cumpleaños, fiestas del pijama, meriendas, fiestas privadas... Son fiestas tematizadas y súper divertidas.', 'picnic-tipis']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Decoraciones y Photocall', 'En ésta sección te vamos a enseñar diferentes tipos de decoración que tambien sirven para haceros esas fotos para el recuerdo. Esta sección es súper importante porque es totalmente personalizada para cada uno de nuestros clientes. Vosotros nos dais vuestra idea, nosotros lo hacemos realidad.', 'photocall']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Fuente de chocolate', 'Disponemos de una fuente de chocolate de 4 pisos, donde podreis saborear nuestro chocolate con leche belga. Ideal para los mas golosos, todo acompañado de chuchces, bollería, frutas... No puedes dejar pasar por alto este servicio tan dulce.', 'fuente-chocolate']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Mini ferias', 'Un servicio súper divertido para todo tipo de público. Es un servicio que es una novedad súper divertida que nos recuerda a las tómbolas de las ferias. Son módulos de 1 metro X 2 metros de alto, donde podrás jugar entre otros juegos a explota los globos, tira las latas y para los más pequeños el juego pesca los patitos... ¿Quieres pasar un rato súper divertido? Un servicio de animación diferente, original y divertido.', 'mini-ferias']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Palomitero', 'Un servicio, para disfrutar bailando, jugando o como quieras. Tener un palomitero en tu fiesta, boda, comunión, cumpleaños... es ideal. Es un rincón único.', 'palomitero']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Algodón de Azúcar', 'Otro de nuestros servicios dulces más solicitados para los peques, bueno y no tan pequeños, porque a quien no le gusta un pellizco dulce.', 'algodon-azucar']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Glitter Bar', 'Un servicio súper divertido tanto para niños como adultos, ideal para pasar un rato súper divertido. Consta de un tocador con perlas, tatuajes, brillantinas... Quedareis todos super guap@s.', 'glitter-bar']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Regalos personalizados', 'En esta sección iremos subiendo regalos personalizados para todo tipo de celebraciones: cumpleaños, boda, bautizo, comunión, san valentin, navidad... Si quieres regalar algo original y diferente estás en la página adecuada, si encuentras algo nos puedes preguntar y te asesoramos. Visita el apartado de Tienda y podrás comprar lo que quieras', 'regalos-personalizados']
        );

        // ── Quitar los ejemplos de relleno (picsum); se conservan las fotos R2 ya existentes ──
        $this->addSql(
            'DELETE FROM service_example WHERE service_id IN (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) AND image_url LIKE \'https://picsum.photos/%\'',
            ['carrito-hot-dog', 'candy-bar', 'carteles-bienvenida', 'picnic-tipis', 'photocall', 'fuente-chocolate', 'mini-ferias', 'palomitero', 'algodon-azucar', 'glitter-bar', 'regalos-personalizados']
        );

        // ── Fotos reales en R2 (una fila por foto, con su pie de foto original) ──
        // carrito-hot-dog
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['249dd25e18a775bccd1f01be21fbfed2', 'carrito-hot-dog', 'Foto real 1', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/2f422245acdf07359339ec0985255186.jpg', 3]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['8d79a292bb312a1f7082005a5dd320c6', 'carrito-hot-dog', 'Foto real 2', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/6a76116cf274616c1d4f76f3e8d3c73c.jpg', 4]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['f81f1983412280c82441077848cadfee', 'carrito-hot-dog', 'Foto real 3', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/0311a25c0c10a688a7aafe37b4cc8037.jpg', 5]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['4d9937234d1e998b54ed37ba49d7902d', 'carrito-hot-dog', 'Foto real 4', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/fe310a783b6149c27f06a608debf2a9b.jpg', 6]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['cf239a1689029cdd41dfcf3d19965bd5', 'carrito-hot-dog', 'Foto real 5', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/0b32e7b15e78acb470b6ea548e25eeab.jpg', 7]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['3f6c8647996dcf4828ae2b812f1c6101', 'carrito-hot-dog', 'Foto real 6', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/e43929cd6aadcf6b3b1287561ba605f3.jpg', 8]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['046316aa08e6c1241b739f96940cab54', 'carrito-hot-dog', 'Foto real 7', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/95dcf4fa65bbfaf5027ff654e5cfb01c.jpg', 9]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['f6ea528658d5c08af184c2b097a3c0bc', 'carrito-hot-dog', 'Foto real 8', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/d5aa6609a78c1e078dafcc81a7fe2f62.jpg', 10]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['c66ba1372664b9cfd22f6b5f52bd5dee', 'carrito-hot-dog', 'Foto real 9', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/321b24d808bec0429a9840ea1622b447.jpg', 11]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['44daef6f5ef0925a601bcd3c446c9315', 'carrito-hot-dog', 'Foto real 10', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/ebf16f07ae6490edf7730feb21d71f11.jpg', 12]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['8ea7df2d75596e8ed7512a9cd77a8439', 'carrito-hot-dog', 'Foto real 11', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/47d36e9ffe559a44a237f673ff2fef0c.jpg', 13]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['18c7de4f0395b5df08dbe62ba9807adc', 'carrito-hot-dog', '# Carrito de Perritos', '# Carrito de Perritos', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/3055f45595591192ed7f0bb8af94c4f7.jpg', 14]
        );
        // candy-bar
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['62f73798332e72918a62c4caa5210b99', 'candy-bar', 'Candy bar comunion', 'Candy bar comunion', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/21ee1b92fdad7ff46d2dbdfd5008d6d0.jpg', 0]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['c79f3622de086605a4df3a48ea1d1e5e', 'candy-bar', 'Decoracion 15 años', 'Decoracion 15 años', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/7f2a3032603900e773d654edbe205128.jpg', 1]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['2501bd3bd6998144c8c4b573bc523f94', 'candy-bar', 'Candy Bar boda + pared de donuts', 'Candy Bar boda + pared de donuts', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/4b12a73ed84997ec51377fbdc5252aea.jpg', 2]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['c32ff2ff760693f28b915b3300e419d1', 'candy-bar', 'Candy bar cumpleaños peppa Pig', 'Candy bar cumpleaños peppa Pig', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/24dfd8e2af4f1608dd19b1de4f18a72c.jpg', 3]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['ef1651b5d130ce3a25e79f80fa682190', 'candy-bar', 'Candy Bar Comunion doble', 'Candy Bar Comunion doble', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/ffdc65bea58a89b4cb220ec21b204ef8.jpg', 4]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['28b18be32035e33b4f434dcda2d5e568', 'candy-bar', 'Candy Bar comunión futbol', 'Candy Bar comunión futbol', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/7185b1b163315e085eeb77f890f8b1e4.jpg', 5]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['85720ac9bc462ded57f7fa142664f63b', 'candy-bar', 'Candy Bar Boda rústico', 'Candy Bar Boda rústico', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/7666a1b4e46cd36a12adffafd7b8e6ed.jpg', 6]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['b88366032d7021c92c0914220b096797', 'candy-bar', 'Candy Bar comunion Sttich', 'Candy Bar comunion Sttich', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/ad7d78f6f40f548cd25ed8eb5d2eb560.jpg', 7]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['def406b54e87610e4e19131dc0c60995', 'candy-bar', 'Candy bar 50 cumpleaños flores', 'Candy bar 50 cumpleaños flores', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/fcffa3b99ebc85fafcdc755709073d85.jpg', 8]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['6a511418196e665bad395d062e591158', 'candy-bar', 'Candy Bar Boda rústico floral', 'Candy Bar Boda rústico floral', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/aebc19e1d13a80423cc9d99472c25705.jpg', 9]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['1c8eb9576fb15c7de1719fb7e7dbbc91', 'candy-bar', 'Candy Bar cumpleaños Sttich', 'Candy Bar cumpleaños Sttich', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/d3f6bfdf09ae9e75e78d07751a2942e3.jpg', 10]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['66d95eaf6b418568bab211cb275d90c2', 'candy-bar', 'Candy Bar comunión Harry Poter', 'Candy Bar comunión Harry Poter', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/ba2f22193a4f55fb11ff5dec8e979e85.jpg', 11]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['6e34dabdfeee6ab76477fbfcbb848252', 'candy-bar', 'Candy Bar comunión Sttich', 'Candy Bar comunión Sttich', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/e66df495359485c85027dbee506d1156.jpg', 12]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['03d5d08eb2bdb883415140d502131c6f', 'candy-bar', 'Candy Bar comunion Hexagono', 'Candy Bar comunion Hexagono', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/a76f763f939fe91478f506fbd8073af1.jpg', 13]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['270c7a3100ae47f749dc6fcd052c2858', 'candy-bar', 'Candy Bar comunion Baloncesto', 'Candy Bar comunion Baloncesto', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/f09845b21e896700c1638b924f0972c3.jpg', 14]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['b0ad2149b2161b9fff9bec1b8f483e99', 'candy-bar', 'Candy Bar comunión Lilo & Sttich', 'Candy Bar comunión Lilo & Sttich', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/9ea342631f65e52708040aa71a76a88d.jpg', 15]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['0cc66116feae278c653ab6b4be2a8cc4', 'candy-bar', 'Candy Bar Boda cortina luces', 'Candy Bar Boda cortina luces', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/e5007abe60d89b0da79b3b1a7504679c.jpg', 16]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['9ac038638d6d68e52a1b0ad781f66b19', 'candy-bar', 'Candy Bar cumpleaños Minnie', 'Candy Bar cumpleaños Minnie', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/6a2861bfcd263c994f5be577e69f0548.jpg', 17]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['e67fc3df98e97f286c80936d87b38681', 'candy-bar', 'Candy Bar Bautizo Nala', 'Candy Bar Bautizo Nala', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/710df174bdfcc6eaf8b185f5b5262306.jpg', 18]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['8a529ffd07b4f19a204239a6e1d57091', 'candy-bar', 'Candy Bar Comunion Cajas', 'Candy Bar Comunion Cajas', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/71f2c9510466bdccc03b9484fc7d5c90.jpg', 19]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['30d092e2586487ab5331f83f411c2972', 'candy-bar', 'Candy Bar comunión Niña', 'Candy Bar comunión Niña', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/23b6b639d533c4a1c88ed5b51682c9fb.jpg', 20]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['5942497e926b45b5679391945dab30bd', 'candy-bar', 'Candy bar bautizo Tematizado', 'Candy bar bautizo Tematizado', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/216a1177923fd735cb96331f6d33f40c.jpg', 21]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['af81eb5b39972d10a21c3bb27aee84b9', 'candy-bar', 'Candy bar comunión', 'Candy bar comunión', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/7a2ea42bdf07683e268f61d07e6b5db0.jpg', 22]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['b4fa1cefa8dfb642cb5334cdcb0c2599', 'candy-bar', 'Candy Bar cumpleaños', 'Candy Bar cumpleaños', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/1cc8f62c7170cf6b256241487f8a2bc2.jpg', 23]
        );
        // carteles-bienvenida
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['b091138d4474148fe020d5306c6d9c25', 'carteles-bienvenida', 'Cartel de bienvenida Boda', 'Cartel de bienvenida Boda', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/3826402513abb8e024966f237bef5566.jpg', 0]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['d9fad10e18118f248866bb5ce9b2ec42', 'carteles-bienvenida', 'Cartel de bienvenida comunión en tonos rosas', 'Cartel de bienvenida comunión en tonos rosas', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/5534a62f42d8c2eaf2fb5c6c8f5cd7df.jpg', 1]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['c672d31a3da82a60c49d32fdfb344dcd', 'carteles-bienvenida', 'Cartel de bienvenida de foto', 'Cartel de bienvenida de foto', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/0f8c82cb41fb338c3b1db3a3c3ea4424.jpg', 2]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['868ad9b0304551f02f27a3068c63f4a5', 'carteles-bienvenida', 'Cartel bienvenida boda tematizado', 'Cartel bienvenida boda tematizado', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/2474d88cdf7b21cb5bcd8c0b9b2e6934.jpg', 3]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['1db82a4afdc7b36e2de06c2357445ace', 'carteles-bienvenida', 'Cartel bienvenida boda', 'Cartel bienvenida boda', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/720aec981f9a64b9ba23cfe1181753d7.jpg', 4]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['c3a1bf2bbd3e60d78fa04982f41cf42e', 'carteles-bienvenida', 'Cartel de Bienvenida bautizo', 'Cartel de Bienvenida bautizo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/a9793d9d8e358a7e597ac0dbeb401907.jpg', 5]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['9343b265c53af6ced20512dcaf6fb222', 'carteles-bienvenida', 'Carteles de Bienvenida foto', 'Carteles de Bienvenida foto', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/b17f5908d285dfcb93fbfe835d7aa663.jpg', 6]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['a4ae3ce1418813a23c41c89e2d4824de', 'carteles-bienvenida', 'Cartel de bienvenida comunión', 'Cartel de bienvenida comunión', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/315b29a43f87401a27117e421f2a0857.jpg', 7]
        );
        // picnic-tipis
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['ce23de3751481dbc1d71ea8dee5b6a7a', 'picnic-tipis', 'Tipi cumpleaños 4', 'Tipi cumpleaños 4', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/5b3bc686e99923cf2e7a795a06b9eb53.jpg', 0]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['90853fc7ccdeb6706ebb99e704a29be7', 'picnic-tipis', 'Picnic con tipi 6 cumpleaños', 'Picnic con tipi 6 cumpleaños', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/727f9feb3e2bd5f6121de4881cc5c943.jpg', 1]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['2b52132a74650d765489acba986a04b6', 'picnic-tipis', 'Picnic cumpleaños', 'Picnic cumpleaños', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/399c6a7aaaa4564024542f70c8a50054.jpg', 2]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['810fae1f010cccc155930612f3cdfd14', 'picnic-tipis', 'Tipi cumpleaños', 'Tipi cumpleaños', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/20d303eff48ae9557be0a3c9f3ae7592.jpg', 3]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['113cecec40c24d9f92963e744c013d94', 'picnic-tipis', 'Tipis', 'Tipis', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/5008191e71195b2813ea8f4f23f7ef8e.jpg', 4]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['25b238606fe15d87f18e55e7781fd232', 'picnic-tipis', 'Picnic', 'Picnic', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/9ca2362d84e6a39cf038d5f0848f0317.jpg', 5]
        );
        // photocall
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['5ea71069f8c0cd192d7d212dc9addb4f', 'photocall', 'Arco decoración', 'Arco decoración', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/b89eeeae83ac40851ae03afa7f79b654.jpg', 0]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['431085e1bc9029f7b75505b574d27542', 'photocall', 'Decoracion futbol', 'Decoracion futbol', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/7fe22f9b75de61cbd4b7592bc50b851f.jpg', 1]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['cbe2ab9304497316dc3c17493e519f60', 'photocall', 'Decoracion espejo 21 años', 'Decoracion espejo 21 años', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/1fa25fcdcd480dc17a213b1f2e391d46.jpg', 2]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['264ac863e8db851ad0d6166a9f5b6dad', 'photocall', 'Decoración corporativa', 'Decoración corporativa', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/7229aabf308ab26a3527722e677c3f86.jpg', 3]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['793a635bc87c3e71ffe0d35308912403', 'photocall', 'Decoración 15 años', 'Decoración 15 años', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/7f2a3032603900e773d654edbe205128.jpg', 4]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['6f8764bd49d27aec83a89f7701bdbe93', 'photocall', 'Decoración navideña', 'Decoración navideña', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/6049f08fdd8cb84a883911beeade9608.jpg', 5]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['15c8aea565501d5cc60eedef112d8192', 'photocall', 'Decoración corporativa', 'Decoración corporativa', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/516df9d5aa87946995d5bf3dc2c86d33.jpg', 6]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['ae17f071315bd878de3566826b41e47a', 'photocall', 'Decoración baby shower', 'Decoración baby shower', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/4b1fd39c757b2855488d06d0e7787c60.jpg', 7]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['d4a5cacec042e94cb628ca546740fca0', 'photocall', 'Decoración corporativa', 'Decoración corporativa', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/376f6a4ae4cfd565f7c05569f105b57f.jpg', 8]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['d2bb0833412109d02a64fcd2239171cb', 'photocall', 'Photocall jardín vertical', 'Photocall jardín vertical', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/b7cc8080f9a06c13aeaaa6ac3af66351.jpg', 9]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['8d20a44989f44b2cbf271409dab2886c', 'photocall', 'Decoracion aro boda', 'Decoracion aro boda', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/b7cf0c61ad10ffc3852b841e2ebaee92.jpg', 10]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['7071549bb764ef7cb153d64a499019c8', 'photocall', 'Decoracion revelación de sexo oso', 'Decoracion revelación de sexo oso', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/8ae946d9828b5b4bd3f4c72e21fad7a9.jpg', 11]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['67d0eb05f4b483f404fa698f904ff6cd', 'photocall', 'Decoración cajas', 'Decoración cajas', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/587cefa93a21e1fc13d3e2c5b69222fb.jpg', 12]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['61cbf74b7e1dec923b3df549545ec188', 'photocall', 'Decoración Halloween', 'Decoración Halloween', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/6c5e12a63e110a3555a4abcc0770de39.jpg', 13]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['0c309723f9250a0cb725912e313a7d2d', 'photocall', 'Aro Bautizo', 'Aro Bautizo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/dbad3673129276ff2c584072f350b057.jpg', 14]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['bcbae67ede34c282138c2eb4ee526e01', 'photocall', 'Caja Barbie', 'Caja Barbie', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/4ead3ba2c5e9a2e3b5780aa239b5cd0f.jpg', 15]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['a6a2a8809b87ec7f8a55bc77a05fac43', 'photocall', 'Decoracion revelación sexo', 'Decoracion revelación sexo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/565afb795576c4453eff051dbaebe991.jpg', 16]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['638d24e75f9eee83602f6c30a8352419', 'photocall', 'Photocall infantil', 'Photocall infantil', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/0e4f63d29df5746f189073e7e5aeab40.jpg', 17]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['cd5b10b67b9ff797e514d06c9f178ad1', 'photocall', 'Aro 18 años', 'Aro 18 años', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/a7aa96b9f42fc4f5c1bbb0a8749d17e5.jpg', 18]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['a60dc68ffe0d575acc21dce1453a64b9', 'photocall', 'Photocall espejo', 'Photocall espejo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/ae17b98d64ad9374cb6367f2d229ae0e.jpg', 19]
        );
        // fuente-chocolate
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['208410e6249b5fbbf5a24a6a7a5ee450', 'fuente-chocolate', 'Foto real 1', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/33f27a9ff9e89556802ece7b5e8882b0.jpg', 0]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['8c6a7b838d4241c3f9a828b5f1936d51', 'fuente-chocolate', 'Foto real 2', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/756ea97768716bc518e0c387f29a681b.jpg', 1]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['4058f480f779ffc3898e520ae156109e', 'fuente-chocolate', 'Foto real 3', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/bfeb9dbffc0f901a8494c1609761bcf3.jpg', 2]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['af9287b00da0d03fc60e09bf20ebdd70', 'fuente-chocolate', 'Foto real 4', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/e7b0078e1e0bdde8749f7774055480be.jpg', 3]
        );
        // mini-ferias
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['6b40af01d254e5ca3c25690dd3494ec1', 'mini-ferias', 'Foto real 1', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/333368fc87f4e4600be64c8f0a0cf755.jpg', 0]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['60e4a90acc06cdfcc416b3f6db60a7f0', 'mini-ferias', 'Foto real 2', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/0c46cc17d93d1a07d19a8318ec15c28d.jpg', 1]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['2edabd1212193a0577e1493477b4c74d', 'mini-ferias', 'Foto real 3', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/46c34ba97464c980576f62465f50498c.jpg', 2]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['c80c91de548143a5f328cef768ea89a6', 'mini-ferias', 'Foto real 4', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/9d5c4d7fd850b8871db5a82ef06ff605.jpg', 3]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['d8dcfe0e37d0343f535160a252ed3b21', 'mini-ferias', 'Foto real 5', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/12dd1cd66dff689c588bb12926cf37b2.jpg', 4]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['8e8c5e68db7c6be9698a9e1f7f363df9', 'mini-ferias', 'Foto real 6', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/00bc69086758dc1839f55a21f6cb5ec7.jpg', 5]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['152e9b0dc58c2c545f2ac198b402b992', 'mini-ferias', 'Foto real 7', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/6a6156f03d307b084890902fa992f5f9.jpg', 6]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['43032accf616e5d7a14133f6e0e01869', 'mini-ferias', 'Foto real 8', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/0d00993ee401933880696a62bc875029.jpg', 7]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['c6f98168c7712d99d3f9a6bd54c62458', 'mini-ferias', 'Foto real 9', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/5d42a0893a2eb6effc5ee898597842f6.jpg', 8]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['8396ad51d5643c06c17cf18e57c8b6fa', 'mini-ferias', 'Foto real 10', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/59e0328a626d8c4f237e447b3f65c554.jpg', 9]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['81cb5f6e5fb69342eb08f16f9632890d', 'mini-ferias', 'Foto real 11', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/99a93bdaa46629babd681dfa59a3dcfe.jpg', 10]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['e76a71641f238f6433a179344c3bfd1e', 'mini-ferias', 'Foto real 12', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/d316c11c3eca6c46c01867711a4c9d68.jpg', 11]
        );
        // palomitero
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['86856ab53d97ff2535f10c915c2cd462', 'palomitero', 'Foto real 1', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/4ecd40fab56f1b2de4819f301f13beb5.jpg', 0]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['5dbcc70d357a8898c8524104a8ed723c', 'palomitero', 'Foto real 2', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/cde0f1496ae030af281543d7d0235ab5.jpg', 1]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['ad496ad8d1bae1946729e38ca7516257', 'palomitero', 'Foto real 3', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/29233c19231bed117d452ee2ab8b169f.jpg', 2]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['3c73f2db78d98a1820ea223950ba0459', 'palomitero', 'Foto real 4', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/72a5b02392bd175262d872caa9825a2c.jpg', 3]
        );
        // algodon-azucar
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['37ba491212dd83827cda9f9dffc3f34f', 'algodon-azucar', 'Foto real 1', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/6dc2a996afe759163f9b03debe7ac35f.jpg', 0]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['a43402d125b43f379707dc234f3c3aa8', 'algodon-azucar', 'Foto real 2', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/2c434047fc275713781af5d1b969ea85.jpg', 1]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['64db8db0a6e820a56ccec929ab38d125', 'algodon-azucar', 'Foto real 3', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/f6cf8bd5784b44bc83cef1f10a1a0fcb.jpg', 2]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['e794ab5695b936c180842e2d4734fe06', 'algodon-azucar', 'Foto real 4', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/9ffc29a2d78782f0e580dfb7f64460cb.jpg', 3]
        );
        // glitter-bar
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['77ecd2a7985c866ba5065b7c093b980c', 'glitter-bar', 'Foto real 1', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/95660104a65f0b4fb0b55d0355059070.jpg', 0]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['1249563b9826e0ebab5c61fc14453a22', 'glitter-bar', 'Foto real 2', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/fe0c8a77cd24b295ef7ba0522846dada.jpg', 1]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['c88108605b29226aa09bc7bf5180f40e', 'glitter-bar', 'Foto real 3', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/87cc0e6349f24b8a4c803d0c71f8a0cf.jpg', 2]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['43991f02dc52247122c78e67d71e2922', 'glitter-bar', 'Foto real 4', 'Ejemplo real de nuestro trabajo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/b01f51fe2ca45dd5108d38922271544c.jpg', 3]
        );
        // regalos-personalizados
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['eb6ad63f2614c415734b1528defa8f71', 'regalos-personalizados', 'Casa seta ratoncito Pérez', 'Casa seta ratoncito Pérez', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/effbabba144f319f0d4dbd371f88db4b.jpg', 0]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['2202d5f52a39bb1c6e700c6526b7b96a', 'regalos-personalizados', 'Puerta ratoncito Pérez', 'Puerta ratoncito Pérez', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/fd5ab14198a72d29d604c27ce0596110.jpg', 1]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['d4221df4c91d1897930ac998cd1b1db0', 'regalos-personalizados', 'Casa del ratoncito pérez estrellas', 'Casa del ratoncito pérez estrellas', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/df9f93b2ccae080459e1ce9c809548be.jpg', 2]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['7cb49705385cff5782a8e67796aea6fd', 'regalos-personalizados', 'Caja personalizada sttich', 'Caja personalizada sttich', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/2b3c8a7539043250f3ceb6db51676b61.jpg', 3]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['2df69bed7bba595d44e4f135fc98b175', 'regalos-personalizados', 'Caja personalizada sonic', 'Caja personalizada sonic', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/425569c40b1ee65cb6f6115771a96480.jpg', 4]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['ef2a83014b2c83fef2b94231adb09003', 'regalos-personalizados', 'Sacos personalizados navideños', 'Sacos personalizados navideños', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/e5823cd2e133c2497ef3c82e5561d3d4.jpg', 5]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['6949f06426d34b7654c83d489448a05f', 'regalos-personalizados', 'Caja burbuja cumpleaños', 'Caja burbuja cumpleaños', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/76353d172515c785f9c602281047e330.jpg', 6]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['1f6793496cadb083a2d5bb56a7aec98c', 'regalos-personalizados', 'Hay un montón de cajas personalizadas disponibles. Preguntanos', 'Hay un montón de cajas personalizadas disponibles. Preguntanos', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/063b8b677cbc3e0ff980724f9fd99bc3.jpg', 7]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['9bf808f5b80e87590ceb7deb7861be19', 'regalos-personalizados', 'Regalo Nutela', 'Regalo Nutela', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/dfbf0b036c9973008e73644956342dec.jpg', 8]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['e0b5014573b5545612982d1dfec0ce1c', 'regalos-personalizados', 'Caja Papá Noel', 'Caja Papá Noel', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/b65c546dc653627bfffb5e08a1bdca7e.jpg', 9]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['018653640156070fccaadab6b3b61552', 'regalos-personalizados', 'Hay más modelos disponibles', 'Hay más modelos disponibles', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/4329cd7f5748027a24d181514ac450da.jpg', 10]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['30a67a3c480d4fd85c5833150f8df0ee', 'regalos-personalizados', 'Caja navideña con taza y bombones', 'Caja navideña con taza y bombones', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/638dbd7fa238c77cf650ce6226879e9f.jpg', 11]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['08f2f30195c07de471b0b8b412da17cd', 'regalos-personalizados', 'Disponibles los tres reyes magos', 'Disponibles los tres reyes magos', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/5ea8ad855318d90626b5c45c3aab3e02.jpg', 12]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['4a393026f4ec0322a868969e2f4b27ad', 'regalos-personalizados', 'Tira de chocolates personalizados', 'Tira de chocolates personalizados', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/136519071ab157a2049da715a77aa524.jpg', 13]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['055da0dd3593bfc546beda9573abc504', 'regalos-personalizados', 'Caja boda chuches', 'Caja boda chuches', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/4247195490d780290012f25e1d48db4a.jpg', 14]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['7cacb81ffdbc14c0eaf7262c311d7967', 'regalos-personalizados', 'Caja personalizada bautizo chuches', 'Caja personalizada bautizo chuches', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/65457c9478e68c601b34da845efc40bf.jpg', 15]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['cc34d00dd175aba469b6dd361891dc93', 'regalos-personalizados', 'Taza comunion con bombones', 'Taza comunion con bombones', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/e1f3288a71e5c815423575d8274cad3f.jpg', 16]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['7134d78beabb8c609521ef5fc9ab8788', 'regalos-personalizados', 'Taza foto comunión', 'Taza foto comunión', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/dc7a41d05a3027d50fc2c4a2ce382f24.jpg', 17]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['6d0f8a40be6eb8d2259ddf14aa92b59a', 'regalos-personalizados', 'Llave madera personalizada', 'Llave madera personalizada', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/5b7433232395d974d052d6d89d307b49.jpg', 18]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['ba056ed1910b2977c25f2bf500c1a834', 'regalos-personalizados', 'Taza bautizo tematizada', 'Taza bautizo tematizada', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/c0271c2c60b35f0d86fb205f03ac464d.jpg', 19]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['0866172e7389bc2e8eb11b759b95a15b', 'regalos-personalizados', 'Taza fin de curso', 'Taza fin de curso', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/06da9437b34eba6f8476ddc0493655dd.jpg', 20]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['a8af4dab07ab4144e1eed045e1858934', 'regalos-personalizados', 'Taza primer año', 'Taza primer año', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/8456e5f8d9462e533a0b2b93d7593feb.jpg', 21]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['6da2175fd848ee510b8c95c4fefb118b', 'regalos-personalizados', 'Taza tematizada comunión', 'Taza tematizada comunión', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/bd009f953e3268efa25c803dade084fa.jpg', 22]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['de3796fb0962b79f50558051a793a155', 'regalos-personalizados', 'Tazas personalizadas', 'Tazas personalizadas', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/ae632d5806e2deccc7812fa73abe6111.jpg', 23]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['d7454b9d04b273fe916b2526fce11d4c', 'regalos-personalizados', 'Taza padrinos', 'Taza padrinos', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/e3e3279499da77cc6b595ae5c50447ea.jpg', 24]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['71b3f447fb3717a273ddd549c62aa0fb', 'regalos-personalizados', 'Taza bautizo', 'Taza bautizo', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/6e94534b7c58018c85fd7c094e6280dc.jpg', 25]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['36ccc6e6bc8056fa31c3d4de0510f646', 'regalos-personalizados', 'Papeleras y cono XXL chuches', 'Papeleras y cono XXL chuches', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/0f76ac4b154ad63e02a6ecd7c0a03d3b.jpg', 26]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['4526fea42970518f8b6650cd25d3ecde', 'regalos-personalizados', 'Abridor vino personalizado', 'Abridor vino personalizado', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/1e706d70b2c75084e5371989a07920dc.jpg', 27]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['f7949b9f3a4c604ae3a3723eb8df3bff', 'regalos-personalizados', 'Abridor imán', 'Abridor imán', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/fd45681e14a96386fdd7c9ebfa3f4721.jpg', 28]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['4a2f440a1d9f0dabfd51a12c5312f077', 'regalos-personalizados', 'Botellas térmicas escudos', 'Botellas térmicas escudos', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/959fddf7fbdf86d69c4f383b330e02bf.jpg', 29]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['34d6a059381d7df91f489ee3300f1f21', 'regalos-personalizados', 'Botellas térmicas para colegio', 'Botellas térmicas para colegio', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/ccc8af8ccdd476ec1a536c4942ce7e8e.jpg', 30]
        );
        $this->addSql(
            'INSERT INTO service_example (id, service_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            ['fd839e2383e52d0e7b1417b297b4d114', 'regalos-personalizados', 'Botella térmica Inicial floral', 'Botella térmica Inicial floral', 'https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/d05e6655dd10d5a5b20a771a85fe0807.jpg', 31]
        );
    }

    public function down(Schema $schema): void
    {
        // Borrar las filas insertadas por esta migración (por id, sin tocar fotos R2 previas)
        $this->addSql('DELETE FROM service_example WHERE id IN (\'249dd25e18a775bccd1f01be21fbfed2\', \'8d79a292bb312a1f7082005a5dd320c6\', \'f81f1983412280c82441077848cadfee\', \'4d9937234d1e998b54ed37ba49d7902d\', \'cf239a1689029cdd41dfcf3d19965bd5\', \'3f6c8647996dcf4828ae2b812f1c6101\', \'046316aa08e6c1241b739f96940cab54\', \'f6ea528658d5c08af184c2b097a3c0bc\', \'c66ba1372664b9cfd22f6b5f52bd5dee\', \'44daef6f5ef0925a601bcd3c446c9315\', \'8ea7df2d75596e8ed7512a9cd77a8439\', \'18c7de4f0395b5df08dbe62ba9807adc\', \'62f73798332e72918a62c4caa5210b99\', \'c79f3622de086605a4df3a48ea1d1e5e\', \'2501bd3bd6998144c8c4b573bc523f94\', \'c32ff2ff760693f28b915b3300e419d1\', \'ef1651b5d130ce3a25e79f80fa682190\', \'28b18be32035e33b4f434dcda2d5e568\', \'85720ac9bc462ded57f7fa142664f63b\', \'b88366032d7021c92c0914220b096797\', \'def406b54e87610e4e19131dc0c60995\', \'6a511418196e665bad395d062e591158\', \'1c8eb9576fb15c7de1719fb7e7dbbc91\', \'66d95eaf6b418568bab211cb275d90c2\', \'6e34dabdfeee6ab76477fbfcbb848252\', \'03d5d08eb2bdb883415140d502131c6f\', \'270c7a3100ae47f749dc6fcd052c2858\', \'b0ad2149b2161b9fff9bec1b8f483e99\', \'0cc66116feae278c653ab6b4be2a8cc4\', \'9ac038638d6d68e52a1b0ad781f66b19\', \'e67fc3df98e97f286c80936d87b38681\', \'8a529ffd07b4f19a204239a6e1d57091\', \'30d092e2586487ab5331f83f411c2972\', \'5942497e926b45b5679391945dab30bd\', \'af81eb5b39972d10a21c3bb27aee84b9\', \'b4fa1cefa8dfb642cb5334cdcb0c2599\', \'b091138d4474148fe020d5306c6d9c25\', \'d9fad10e18118f248866bb5ce9b2ec42\', \'c672d31a3da82a60c49d32fdfb344dcd\', \'868ad9b0304551f02f27a3068c63f4a5\', \'1db82a4afdc7b36e2de06c2357445ace\', \'c3a1bf2bbd3e60d78fa04982f41cf42e\', \'9343b265c53af6ced20512dcaf6fb222\', \'a4ae3ce1418813a23c41c89e2d4824de\', \'ce23de3751481dbc1d71ea8dee5b6a7a\', \'90853fc7ccdeb6706ebb99e704a29be7\', \'2b52132a74650d765489acba986a04b6\', \'810fae1f010cccc155930612f3cdfd14\', \'113cecec40c24d9f92963e744c013d94\', \'25b238606fe15d87f18e55e7781fd232\', \'5ea71069f8c0cd192d7d212dc9addb4f\', \'431085e1bc9029f7b75505b574d27542\', \'cbe2ab9304497316dc3c17493e519f60\', \'264ac863e8db851ad0d6166a9f5b6dad\', \'793a635bc87c3e71ffe0d35308912403\', \'6f8764bd49d27aec83a89f7701bdbe93\', \'15c8aea565501d5cc60eedef112d8192\', \'ae17f071315bd878de3566826b41e47a\', \'d4a5cacec042e94cb628ca546740fca0\', \'d2bb0833412109d02a64fcd2239171cb\', \'8d20a44989f44b2cbf271409dab2886c\', \'7071549bb764ef7cb153d64a499019c8\', \'67d0eb05f4b483f404fa698f904ff6cd\', \'61cbf74b7e1dec923b3df549545ec188\', \'0c309723f9250a0cb725912e313a7d2d\', \'bcbae67ede34c282138c2eb4ee526e01\', \'a6a2a8809b87ec7f8a55bc77a05fac43\', \'638d24e75f9eee83602f6c30a8352419\', \'cd5b10b67b9ff797e514d06c9f178ad1\', \'a60dc68ffe0d575acc21dce1453a64b9\', \'208410e6249b5fbbf5a24a6a7a5ee450\', \'8c6a7b838d4241c3f9a828b5f1936d51\', \'4058f480f779ffc3898e520ae156109e\', \'af9287b00da0d03fc60e09bf20ebdd70\', \'6b40af01d254e5ca3c25690dd3494ec1\', \'60e4a90acc06cdfcc416b3f6db60a7f0\', \'2edabd1212193a0577e1493477b4c74d\', \'c80c91de548143a5f328cef768ea89a6\', \'d8dcfe0e37d0343f535160a252ed3b21\', \'8e8c5e68db7c6be9698a9e1f7f363df9\', \'152e9b0dc58c2c545f2ac198b402b992\', \'43032accf616e5d7a14133f6e0e01869\', \'c6f98168c7712d99d3f9a6bd54c62458\', \'8396ad51d5643c06c17cf18e57c8b6fa\', \'81cb5f6e5fb69342eb08f16f9632890d\', \'e76a71641f238f6433a179344c3bfd1e\', \'86856ab53d97ff2535f10c915c2cd462\', \'5dbcc70d357a8898c8524104a8ed723c\', \'ad496ad8d1bae1946729e38ca7516257\', \'3c73f2db78d98a1820ea223950ba0459\', \'37ba491212dd83827cda9f9dffc3f34f\', \'a43402d125b43f379707dc234f3c3aa8\', \'64db8db0a6e820a56ccec929ab38d125\', \'e794ab5695b936c180842e2d4734fe06\', \'77ecd2a7985c866ba5065b7c093b980c\', \'1249563b9826e0ebab5c61fc14453a22\', \'c88108605b29226aa09bc7bf5180f40e\', \'43991f02dc52247122c78e67d71e2922\', \'eb6ad63f2614c415734b1528defa8f71\', \'2202d5f52a39bb1c6e700c6526b7b96a\', \'d4221df4c91d1897930ac998cd1b1db0\', \'7cb49705385cff5782a8e67796aea6fd\', \'2df69bed7bba595d44e4f135fc98b175\', \'ef2a83014b2c83fef2b94231adb09003\', \'6949f06426d34b7654c83d489448a05f\', \'1f6793496cadb083a2d5bb56a7aec98c\', \'9bf808f5b80e87590ceb7deb7861be19\', \'e0b5014573b5545612982d1dfec0ce1c\', \'018653640156070fccaadab6b3b61552\', \'30a67a3c480d4fd85c5833150f8df0ee\', \'08f2f30195c07de471b0b8b412da17cd\', \'4a393026f4ec0322a868969e2f4b27ad\', \'055da0dd3593bfc546beda9573abc504\', \'7cacb81ffdbc14c0eaf7262c311d7967\', \'cc34d00dd175aba469b6dd361891dc93\', \'7134d78beabb8c609521ef5fc9ab8788\', \'6d0f8a40be6eb8d2259ddf14aa92b59a\', \'ba056ed1910b2977c25f2bf500c1a834\', \'0866172e7389bc2e8eb11b759b95a15b\', \'a8af4dab07ab4144e1eed045e1858934\', \'6da2175fd848ee510b8c95c4fefb118b\', \'de3796fb0962b79f50558051a793a155\', \'d7454b9d04b273fe916b2526fce11d4c\', \'71b3f447fb3717a273ddd549c62aa0fb\', \'36ccc6e6bc8056fa31c3d4de0510f646\', \'4526fea42970518f8b6650cd25d3ecde\', \'f7949b9f3a4c604ae3a3723eb8df3bff\', \'4a2f440a1d9f0dabfd51a12c5312f077\', \'34d6a059381d7df91f489ee3300f1f21\', \'fd839e2383e52d0e7b1417b297b4d114\')');

        // Restaurar los textos de relleno anteriores
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Carrito de Hot Dog', 'Un clásico reinventado. Nuestro carrito de perritos calientes es el hit de cualquier celebración, con panes artesanales y toppings para todos los gustos.', 'carrito-hot-dog']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Candy Bar', 'Una explosión de color y dulzura. Decoramos tu espacio con una barra de dulces personalizada que combina perfectamente con la estética de tu evento.', 'candy-bar']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Carteles de Bienvenida', 'El primer detalle que verán tus invitados. Creamos carteles únicos y personalizados que dan la bienvenida con estilo a cualquier celebración.', 'carteles-bienvenida']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Picnic & Tipis', 'Crea un ambiente mágico y acogedor. Nuestros sets de picnic con tipis son perfectos para celebraciones al aire libre y momentos únicos.', 'picnic-tipis']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Photocall & Decoraciones', 'El rincón perfecto para los mejores recuerdos. Diseñamos photocalls y decoraciones temáticas que se convierten en el corazón visual de tu evento.', 'photocall']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Fuente de Chocolate', 'La estrella de los postres. Una fuente de chocolate con fondue, acompañada de frutas, bizcochos y marshmallows para una experiencia deliciosa.', 'fuente-chocolate']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Mini Ferias', 'La diversión de la feria en tu evento. Juegos tradicionales, actividades para niños y adultos que crean un ambiente festivo y lleno de alegría.', 'mini-ferias']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Palomitero', 'El olor y el sabor que todos conocen. Nuestra máquina de palomitas de maíz llena cualquier espacio de aroma y alegría en el momento exacto.', 'palomitero']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Algodón de Azúcar', 'Nubes de azúcar que hacen volar la imaginación. El algodón de azúcar artesanal es una experiencia sensorial que encanta a pequeños y mayores.', 'algodon-azucar']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Glitter Bar', 'Brillar nunca fue tan fácil. Nuestro Glitter Bar aplica brillo cosmético de forma profesional para que tus invitados luzcan increíbles en cada foto.', 'glitter-bar']
        );
        $this->addSql(
            'UPDATE service SET name = ?, description = ? WHERE id = ?',
            ['Regalos Personalizados', 'El detalle que hace la diferencia. Creamos regalos y detalles personalizados para tus invitados, desde packaging hasta productos únicos con tu nombre.', 'regalos-personalizados']
        );

        // Nota: los ejemplos picsum borrados en up() no se restauran (eran placeholders).
    }
}
