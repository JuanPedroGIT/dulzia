# Contenido de la web original — dulziasalamancaeventos.com

> Copia de los textos y fotos de la web original (WordPress) recopilada el 2026-09-24
> para el rediseño de la web. Los textos se copian **literales** (incluidos errores
> tipográficos del original).

## Dónde está cada cosa

- **Textos y fotos de los 11 servicios** → ya en la BD (`service` y `service_example`),
  aplicados por la migración `backend/migrations/Version20260924120000.php`.
- **Fotos de servicios** → bucket R2, keys `services/<hex>.<ext>` (ver tablas por servicio abajo).
- **Fotos de la home** → bucket R2, keys `site/*` (sin tabla en BD).
- **Resto de textos** (home, nosotros, contacto, legal) → este documento, pendiente de maquetar.

## Datos de contacto (de la web original)

- WhatsApp: +34 629 991 659 → https://wa.me/34629991659
- Teléfono: +34 629 991 659
- Email: info@dulziasalamancaeventos.com
- Facebook: https://www.facebook.com/profile.php?id=61569180747614
- Instagram: https://www.instagram.com/dulziasala

---

# 1. Home (`/`)

## Hero

- Imagen: `https://www.dulziasalamancaeventos.com/wp-content/uploads/go-x/u/ca680a8a-e596-4540-b9a8-65693eaeb771/l0,t393,w900,h813/image-768x694.jpg`
- En bucket R2: `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/site/hero.jpg`

## Sección «Quiénes somos»

### !Bienvenidos a nuestro mundo de Eventos!

Somos Yoana y Sergio, dos apasionados de la decoración y la organización de momentos únicos. Nos dedicamos a transformar cada celebración en algo especial, cuidando cada detalle para que tú solo tengas que disfrutar.

Decoramos todo tipo de eventos: Bodas, Comuniones, cumpleaños, fiestas privadas, corporativas... Ademas, contamos con nuestro divertido carrito de Perritos Calientes, crepes..., para darle un toque original y delicioso a tú celebración.

Nos encanta nuestro trabajo y ponemos el corazón en cada proyecto, porque creemos que cada evento es una oportunidad para crear recuerdos inolvidables.

Si buscas cercanía, creatividad y pasión, ¡Has llegado al lugar indicado!

Cuéntanos tu próximo evento, y hagamos de ese día algo mágico.

¿TE ATREVES?

## Galería (3 fotos tras «¿TE ATREVES?»)

| # | URL original | En bucket R2 |
|---|---|---|
| 1 | `https://www.dulziasalamancaeventos.com/wp-content/uploads/go-x/u/3fd3b50c-59ee-4c00-85bf-2b6ad96f3c0f/l250,t0,w1500,h1500/image-768x768.jpg` | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/site/galeria-1.jpg` |
| 2 | `https://www.dulziasalamancaeventos.com/wp-content/uploads/go-x/u/7176e75d-c901-43ed-89a0-57b259613641/l0,t250,w1500,h1500/image-768x768.jpg` | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/site/galeria-2.jpg` |
| 3 | `https://www.dulziasalamancaeventos.com/wp-content/uploads/go-x/u/052fe5f2-7875-4826-8bf0-13a413b31020/l250,t0,w1500,h1500/image-768x768.jpg` | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/site/galeria-3.jpg` |

## Sección «Bodas»

Bodas

Crea en tu boda un rincón mágico...

Este rincón está diseñado para que disfrutan todos tus invitados.

Algo original, bonito y personalizado

Un rincón que no pasará desapercibido por ninguno de tus invitados, y que podrán disfrutar tanto los más pequeños como los adultos y que lo recordarán pasados los días...

Este espacio cuenta con un fondo personalizado y una mesa dulce donde podrán endulzarse durante todo el evento.

Además de un montón de servicios que podemos ofrecerte para que ese día sea único

## Sección «Eventos Especiales»

Eventos Especiales

Tú lo sueñas, nosotros lo hacemos posible

Danos tu idea, boceto, ilusión, locura ... y Dulzia Salamanca Eventos lo hará posible.

- ¿Un carrito de comida en directo?
- ¿Un Candy bar especial?
- ¿Quieres hacer un picnic al aire libre?
- ¿Quieres sorprender a alguien?
- ¿Quieres un día completo de sorpresas?
- ¿Quieres sorprender a tu pareja por tu aniversario?
- ¿Quieres una decoracion especial?

Nos adaptamos a tus necesidades.

## Sección «Comuniones, Bautizo, Cumpleaños, Baby shower...»

Comuniones, Bautizo, Cumpleaños,

Baby shower...

Dale a los niños el protagonismo que merecen

¡¡¡En este apartado los protagonistas son los niños!!!

Dales el lugar que se merecen.

Con nuestro rincón los peques volarán a un mundo de fantasía.

## Valores

Pasión — Dedicación — Detalle — Ilusión

## Testimonios («Lo que nuestros clientes dicen de nosotros»)

Intro: «Si buscas la combinación perfecta entre profesionalidad, calidad de productos y cuidado de los detalles para cualquier celebración Dulziasalamancaeventos es tu mejor opción.»

### Elsa Diaz Ravelo

«Sergio y Yoana escuchan al cliente; atienden cualquier duda o sugerencia; y te transmiten confianza y seguridad desde el primer contacto; transmitiendo en que estás contratando servicios profesionales de primera. Habíamos hablado “todo”: elegido los detalles, los dulces para la Candy bar,… y aún así, mi hija no fue la única sorprendida, pues yo misma quedé impresionada porque superaron todas mis expectativas; y todos los invitados quedaron encantados. Sin dudarlo, los recomendaría; y contaría con ellos nuevamente, para cualquier celebración que quisiera endulzar y con la que sorprender a mis invitados.»

### Guiomar Pérez

«Lo contraté a través de Instagram y, maravilloso. Quedó súper bonito!. Tenía un motivo de harry potter y superó nuestras expectativas!!!. Súper recomendable»

### Elena Pindado

«Desde el primer contacto con ellos, fueron amables, y con mucha paciencia jajaja. El día del evento fueron muy profesionales y súper amables con los invitados, los recomiendo 100% por los productos, el trabajo, el precio y como no, por el trato recibido. Gracias por iluminar nuestro gran día con vuestro gran trabajo»

- Avatar (misma imagen en las 3 reseñas): `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/site/avatar-testimonio.png`
- Original: `https://www.dulziasalamancaeventos.com/wp-content/uploads/go-x/u/9f983b5c-0039-4951-afc4-464d993feec0/l0,t0,w360,h360/image.png`

---

# 2. Quiénes somos (`/quienes-somos/`)

Misma sección «Quiénes somos» de la home (ver arriba) con la imagen del hero.

---

# 3. Contacto (`/contacto/`)

## Datos visibles

- WhatsApp: +34 629 991 659
- Email: info@dulziasalamancaeventos.com
- Teléfono: +34 629 991 659

## Formulario (newsletter/contacto) — campos y textos

- «Suscríbase a nuestra newsletter»
- Nombre* (obligatorio)
- Telefono* (obligatorio)
- Fecha del evento* (obligatorio)
- ¿Cómo nos has conocido?* (obligatorio)
- Correo electrónico* (obligatorio; error: «La dirección de correo electrónico no es válida»)
- Mensaje* (obligatorio)
- Checkbox: «Estoy de acuerdo en que estos datos se almacenen y procesen con el fin de establecer contacto. Soy consciente de que puedo revocar mi consentimiento en cualquier momento.*»
- Nota: «* Indica los campos obligatorios»
- Botón: «Envía»
- Error: «Hubo un error al enviar su mensaje. Por favor, inténtelo de nuevo.»
- Éxito: «¡Gracias! Nos pondremos en contacto con usted lo antes posible.»

---

# 4. Servicios (11 páginas)

Textos (nombre + descripción) ya aplicados en la BD por la migración. Las galerías de fotos
también están en la BD (`service_example`) con sus pies de foto originales.

Inventario de fotos por servicio (pie de foto original → objeto en R2):

## Carrito de Perritos (`carrito-hot-dog`) — 12 fotos

| # | Pie de foto | Objeto en R2 |
|---|---|---|
| 1 | Foto real 1 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/2f422245acdf07359339ec0985255186.jpg` |
| 2 | Foto real 2 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/6a76116cf274616c1d4f76f3e8d3c73c.jpg` |
| 3 | Foto real 3 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/0311a25c0c10a688a7aafe37b4cc8037.jpg` |
| 4 | Foto real 4 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/fe310a783b6149c27f06a608debf2a9b.jpg` |
| 5 | Foto real 5 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/0b32e7b15e78acb470b6ea548e25eeab.jpg` |
| 6 | Foto real 6 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/e43929cd6aadcf6b3b1287561ba605f3.jpg` |
| 7 | Foto real 7 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/95dcf4fa65bbfaf5027ff654e5cfb01c.jpg` |
| 8 | Foto real 8 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/d5aa6609a78c1e078dafcc81a7fe2f62.jpg` |
| 9 | Foto real 9 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/321b24d808bec0429a9840ea1622b447.jpg` |
| 10 | Foto real 10 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/ebf16f07ae6490edf7730feb21d71f11.jpg` |
| 11 | Foto real 11 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/47d36e9ffe559a44a237f673ff2fef0c.jpg` |
| 12 | # Carrito de Perritos | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/3055f45595591192ed7f0bb8af94c4f7.jpg` |

## Candy Bar (`candy-bar`) — 24 fotos

| # | Pie de foto | Objeto en R2 |
|---|---|---|
| 1 | Candy bar comunion | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/21ee1b92fdad7ff46d2dbdfd5008d6d0.jpg` |
| 2 | Decoracion 15 años | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/7f2a3032603900e773d654edbe205128.jpg` |
| 3 | Candy Bar boda + pared de donuts | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/4b12a73ed84997ec51377fbdc5252aea.jpg` |
| 4 | Candy bar cumpleaños peppa Pig | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/24dfd8e2af4f1608dd19b1de4f18a72c.jpg` |
| 5 | Candy Bar Comunion doble | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/ffdc65bea58a89b4cb220ec21b204ef8.jpg` |
| 6 | Candy Bar comunión futbol | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/7185b1b163315e085eeb77f890f8b1e4.jpg` |
| 7 | Candy Bar Boda rústico | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/7666a1b4e46cd36a12adffafd7b8e6ed.jpg` |
| 8 | Candy Bar comunion Sttich | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/ad7d78f6f40f548cd25ed8eb5d2eb560.jpg` |
| 9 | Candy bar 50 cumpleaños flores | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/fcffa3b99ebc85fafcdc755709073d85.jpg` |
| 10 | Candy Bar Boda rústico floral | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/aebc19e1d13a80423cc9d99472c25705.jpg` |
| 11 | Candy Bar cumpleaños Sttich | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/d3f6bfdf09ae9e75e78d07751a2942e3.jpg` |
| 12 | Candy Bar comunión Harry Poter | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/ba2f22193a4f55fb11ff5dec8e979e85.jpg` |
| 13 | Candy Bar comunión Sttich | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/e66df495359485c85027dbee506d1156.jpg` |
| 14 | Candy Bar comunion Hexagono | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/a76f763f939fe91478f506fbd8073af1.jpg` |
| 15 | Candy Bar comunion Baloncesto | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/f09845b21e896700c1638b924f0972c3.jpg` |
| 16 | Candy Bar comunión Lilo & Sttich | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/9ea342631f65e52708040aa71a76a88d.jpg` |
| 17 | Candy Bar Boda cortina luces | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/e5007abe60d89b0da79b3b1a7504679c.jpg` |
| 18 | Candy Bar cumpleaños Minnie | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/6a2861bfcd263c994f5be577e69f0548.jpg` |
| 19 | Candy Bar Bautizo Nala | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/710df174bdfcc6eaf8b185f5b5262306.jpg` |
| 20 | Candy Bar Comunion Cajas | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/71f2c9510466bdccc03b9484fc7d5c90.jpg` |
| 21 | Candy Bar comunión Niña | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/23b6b639d533c4a1c88ed5b51682c9fb.jpg` |
| 22 | Candy bar bautizo Tematizado | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/216a1177923fd735cb96331f6d33f40c.jpg` |
| 23 | Candy bar comunión | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/7a2ea42bdf07683e268f61d07e6b5db0.jpg` |
| 24 | Candy Bar cumpleaños | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/1cc8f62c7170cf6b256241487f8a2bc2.jpg` |

## Carteles de Bienvenida (`carteles-bienvenida`) — 8 fotos

| # | Pie de foto | Objeto en R2 |
|---|---|---|
| 1 | Cartel de bienvenida Boda | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/3826402513abb8e024966f237bef5566.jpg` |
| 2 | Cartel de bienvenida comunión en tonos rosas | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/5534a62f42d8c2eaf2fb5c6c8f5cd7df.jpg` |
| 3 | Cartel de bienvenida de foto | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/0f8c82cb41fb338c3b1db3a3c3ea4424.jpg` |
| 4 | Cartel bienvenida boda tematizado | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/2474d88cdf7b21cb5bcd8c0b9b2e6934.jpg` |
| 5 | Cartel bienvenida boda | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/720aec981f9a64b9ba23cfe1181753d7.jpg` |
| 6 | Cartel de Bienvenida bautizo | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/a9793d9d8e358a7e597ac0dbeb401907.jpg` |
| 7 | Carteles de Bienvenida foto | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/b17f5908d285dfcb93fbfe835d7aa663.jpg` |
| 8 | Cartel de bienvenida comunión | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/315b29a43f87401a27117e421f2a0857.jpg` |

## Picnic y Tipis (`picnic-tipis`) — 6 fotos

| # | Pie de foto | Objeto en R2 |
|---|---|---|
| 1 | Tipi cumpleaños 4 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/5b3bc686e99923cf2e7a795a06b9eb53.jpg` |
| 2 | Picnic con tipi 6 cumpleaños | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/727f9feb3e2bd5f6121de4881cc5c943.jpg` |
| 3 | Picnic cumpleaños | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/399c6a7aaaa4564024542f70c8a50054.jpg` |
| 4 | Tipi cumpleaños | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/20d303eff48ae9557be0a3c9f3ae7592.jpg` |
| 5 | Tipis | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/5008191e71195b2813ea8f4f23f7ef8e.jpg` |
| 6 | Picnic | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/9ca2362d84e6a39cf038d5f0848f0317.jpg` |

## Decoraciones y Photocall (`photocall`) — 20 fotos

| # | Pie de foto | Objeto en R2 |
|---|---|---|
| 1 | Arco decoración | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/b89eeeae83ac40851ae03afa7f79b654.jpg` |
| 2 | Decoracion futbol | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/7fe22f9b75de61cbd4b7592bc50b851f.jpg` |
| 3 | Decoracion espejo 21 años | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/1fa25fcdcd480dc17a213b1f2e391d46.jpg` |
| 4 | Decoración corporativa | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/7229aabf308ab26a3527722e677c3f86.jpg` |
| 5 | Decoración 15 años | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/7f2a3032603900e773d654edbe205128.jpg` |
| 6 | Decoración navideña | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/6049f08fdd8cb84a883911beeade9608.jpg` |
| 7 | Decoración corporativa | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/516df9d5aa87946995d5bf3dc2c86d33.jpg` |
| 8 | Decoración baby shower | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/4b1fd39c757b2855488d06d0e7787c60.jpg` |
| 9 | Decoración corporativa | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/376f6a4ae4cfd565f7c05569f105b57f.jpg` |
| 10 | Photocall jardín vertical | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/b7cc8080f9a06c13aeaaa6ac3af66351.jpg` |
| 11 | Decoracion aro boda | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/b7cf0c61ad10ffc3852b841e2ebaee92.jpg` |
| 12 | Decoracion revelación de sexo oso | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/8ae946d9828b5b4bd3f4c72e21fad7a9.jpg` |
| 13 | Decoración cajas | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/587cefa93a21e1fc13d3e2c5b69222fb.jpg` |
| 14 | Decoración Halloween | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/6c5e12a63e110a3555a4abcc0770de39.jpg` |
| 15 | Aro Bautizo | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/dbad3673129276ff2c584072f350b057.jpg` |
| 16 | Caja Barbie | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/4ead3ba2c5e9a2e3b5780aa239b5cd0f.jpg` |
| 17 | Decoracion revelación sexo | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/565afb795576c4453eff051dbaebe991.jpg` |
| 18 | Photocall infantil | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/0e4f63d29df5746f189073e7e5aeab40.jpg` |
| 19 | Aro 18 años | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/a7aa96b9f42fc4f5c1bbb0a8749d17e5.jpg` |
| 20 | Photocall espejo | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/ae17b98d64ad9374cb6367f2d229ae0e.jpg` |

## Fuente de chocolate (`fuente-chocolate`) — 4 fotos

| # | Pie de foto | Objeto en R2 |
|---|---|---|
| 1 | Foto real 1 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/33f27a9ff9e89556802ece7b5e8882b0.jpg` |
| 2 | Foto real 2 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/756ea97768716bc518e0c387f29a681b.jpg` |
| 3 | Foto real 3 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/bfeb9dbffc0f901a8494c1609761bcf3.jpg` |
| 4 | Foto real 4 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/e7b0078e1e0bdde8749f7774055480be.jpg` |

## Mini ferias (`mini-ferias`) — 12 fotos

| # | Pie de foto | Objeto en R2 |
|---|---|---|
| 1 | Foto real 1 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/333368fc87f4e4600be64c8f0a0cf755.jpg` |
| 2 | Foto real 2 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/0c46cc17d93d1a07d19a8318ec15c28d.jpg` |
| 3 | Foto real 3 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/46c34ba97464c980576f62465f50498c.jpg` |
| 4 | Foto real 4 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/9d5c4d7fd850b8871db5a82ef06ff605.jpg` |
| 5 | Foto real 5 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/12dd1cd66dff689c588bb12926cf37b2.jpg` |
| 6 | Foto real 6 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/00bc69086758dc1839f55a21f6cb5ec7.jpg` |
| 7 | Foto real 7 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/6a6156f03d307b084890902fa992f5f9.jpg` |
| 8 | Foto real 8 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/0d00993ee401933880696a62bc875029.jpg` |
| 9 | Foto real 9 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/5d42a0893a2eb6effc5ee898597842f6.jpg` |
| 10 | Foto real 10 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/59e0328a626d8c4f237e447b3f65c554.jpg` |
| 11 | Foto real 11 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/99a93bdaa46629babd681dfa59a3dcfe.jpg` |
| 12 | Foto real 12 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/d316c11c3eca6c46c01867711a4c9d68.jpg` |

## Palomitero (`palomitero`) — 4 fotos

| # | Pie de foto | Objeto en R2 |
|---|---|---|
| 1 | Foto real 1 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/4ecd40fab56f1b2de4819f301f13beb5.jpg` |
| 2 | Foto real 2 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/cde0f1496ae030af281543d7d0235ab5.jpg` |
| 3 | Foto real 3 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/29233c19231bed117d452ee2ab8b169f.jpg` |
| 4 | Foto real 4 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/72a5b02392bd175262d872caa9825a2c.jpg` |

## Algodón de Azúcar (`algodon-azucar`) — 4 fotos

| # | Pie de foto | Objeto en R2 |
|---|---|---|
| 1 | Foto real 1 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/6dc2a996afe759163f9b03debe7ac35f.jpg` |
| 2 | Foto real 2 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/2c434047fc275713781af5d1b969ea85.jpg` |
| 3 | Foto real 3 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/f6cf8bd5784b44bc83cef1f10a1a0fcb.jpg` |
| 4 | Foto real 4 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/9ffc29a2d78782f0e580dfb7f64460cb.jpg` |

## Glitter Bar (`glitter-bar`) — 4 fotos

| # | Pie de foto | Objeto en R2 |
|---|---|---|
| 1 | Foto real 1 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/95660104a65f0b4fb0b55d0355059070.jpg` |
| 2 | Foto real 2 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/fe0c8a77cd24b295ef7ba0522846dada.jpg` |
| 3 | Foto real 3 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/87cc0e6349f24b8a4c803d0c71f8a0cf.jpg` |
| 4 | Foto real 4 | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/b01f51fe2ca45dd5108d38922271544c.jpg` |

## Regalos personalizados (`regalos-personalizados`) — 32 fotos

| # | Pie de foto | Objeto en R2 |
|---|---|---|
| 1 | Casa seta ratoncito Pérez | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/effbabba144f319f0d4dbd371f88db4b.jpg` |
| 2 | Puerta ratoncito Pérez | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/fd5ab14198a72d29d604c27ce0596110.jpg` |
| 3 | Casa del ratoncito pérez estrellas | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/df9f93b2ccae080459e1ce9c809548be.jpg` |
| 4 | Caja personalizada sttich | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/2b3c8a7539043250f3ceb6db51676b61.jpg` |
| 5 | Caja personalizada sonic | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/425569c40b1ee65cb6f6115771a96480.jpg` |
| 6 | Sacos personalizados navideños | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/e5823cd2e133c2497ef3c82e5561d3d4.jpg` |
| 7 | Caja burbuja cumpleaños | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/76353d172515c785f9c602281047e330.jpg` |
| 8 | Hay un montón de cajas personalizadas disponibles. Preguntanos | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/063b8b677cbc3e0ff980724f9fd99bc3.jpg` |
| 9 | Regalo Nutela | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/dfbf0b036c9973008e73644956342dec.jpg` |
| 10 | Caja Papá Noel | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/b65c546dc653627bfffb5e08a1bdca7e.jpg` |
| 11 | Hay más modelos disponibles | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/4329cd7f5748027a24d181514ac450da.jpg` |
| 12 | Caja navideña con taza y bombones | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/638dbd7fa238c77cf650ce6226879e9f.jpg` |
| 13 | Disponibles los tres reyes magos | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/5ea8ad855318d90626b5c45c3aab3e02.jpg` |
| 14 | Tira de chocolates personalizados | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/136519071ab157a2049da715a77aa524.jpg` |
| 15 | Caja boda chuches | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/4247195490d780290012f25e1d48db4a.jpg` |
| 16 | Caja personalizada bautizo chuches | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/65457c9478e68c601b34da845efc40bf.jpg` |
| 17 | Taza comunion con bombones | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/e1f3288a71e5c815423575d8274cad3f.jpg` |
| 18 | Taza foto comunión | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/dc7a41d05a3027d50fc2c4a2ce382f24.jpg` |
| 19 | Llave madera personalizada | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/5b7433232395d974d052d6d89d307b49.jpg` |
| 20 | Taza bautizo tematizada | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/c0271c2c60b35f0d86fb205f03ac464d.jpg` |
| 21 | Taza fin de curso | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/06da9437b34eba6f8476ddc0493655dd.jpg` |
| 22 | Taza primer año | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/8456e5f8d9462e533a0b2b93d7593feb.jpg` |
| 23 | Taza tematizada comunión | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/bd009f953e3268efa25c803dade084fa.jpg` |
| 24 | Tazas personalizadas | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/ae632d5806e2deccc7812fa73abe6111.jpg` |
| 25 | Taza padrinos | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/e3e3279499da77cc6b595ae5c50447ea.jpg` |
| 26 | Taza bautizo | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/6e94534b7c58018c85fd7c094e6280dc.jpg` |
| 27 | Papeleras y cono XXL chuches | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/0f76ac4b154ad63e02a6ecd7c0a03d3b.jpg` |
| 28 | Abridor vino personalizado | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/1e706d70b2c75084e5371989a07920dc.jpg` |
| 29 | Abridor imán | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/fd45681e14a96386fdd7c9ebfa3f4721.jpg` |
| 30 | Botellas térmicas escudos | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/959fddf7fbdf86d69c4f383b330e02bf.jpg` |
| 31 | Botellas térmicas para colegio | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/ccc8af8ccdd476ec1a536c4942ce7e8e.jpg` |
| 32 | Botella térmica Inicial floral | `https://pub-a68ea1de71d047948bcd76b9a0440e45.r2.dev/services/d05e6655dd10d5a5b20a771a85fe0807.jpg` |

---

# 5. Aviso legal (`/aviso-legal/`)

> Texto literal de la página original (con sus erratas, p. ej. «El usted responderá…»).
> **Nota:** en la página nueva (`frontend/src/pages/AvisoLegalPage.vue`) se han corregido estas
> erratas: «El usted responderá» → «Usted responderá», «DULZIA SALAMANCA EVENTOSB» → «DULZIA
> SALAMANCA EVENTOS», «ORIGINALIA SALAMANCA CB» → «DULZIA SALAMANCA EVENTOS», «xenófobo» →
> «xenófobos» y «acceder datos de otros personas» → «acceder a datos de otras personas».

1. Objeto y aceptación — El presente aviso legal regula el uso de nuestro sitio web, del que es titular DULZIA SALAMANCA EVENTOS. La navegación por el sitio web de DULZIA SALAMANCA EVENTOS atribuye la condición de usuario del mismo e implica la aceptación plena y sin reservas de todas y cada una de las disposiciones incluidas en este Aviso Legal, que pueden sufrir modificaciones. Usted se obliga a hacer un uso correcto del sitio web de conformidad con las leyes, la buena fe, el orden público, los usos del tráfico y el presente Aviso Legal. El usted responderá frente a DULZIA SALAMANCA EVENTOS o frente a terceros, de cualesquiera daños y perjuicios que pudieran causarle como consecuencia del incumplimiento de dicha obligación.

2. Identificación y comunicaciones — DULZIA SALAMANCA EVENTOS, en cumplimiento de la Ley 34/2002, de 11 de julio, de servicios de la sociedad de la información y de comercio electrónico, le informa de que:
- Denominación social: DULZIA SALAMANCA EVENTOS
- CIF: 70900291P
Para comunicarse con nosotros, ponemos a su disposición diferentes medios de contacto especificados en la política de privacidad. Todas las notificaciones y comunicaciones que realice con DULZIA SALAMANCA EVENTOS se considerarán eficaces, a todos los efectos, siempre y cuando se realicen por los medios especificados anteriormente.

3. Condiciones de acceso y utilización — El sitio web y sus servicios son de acceso libre, no obstante, DULZIA SALAMANCA EVENTOS condiciona la utilización de algunos de los servicios ofrecidos en su web a la previa cumplimentación del correspondiente formulario. Usted garantiza la autenticidad y actualidad de todos aquellos datos que comunique a DULZIA SALAMANCA EVENTOS y será el único responsable de las manifestaciones falsas o inexactas que realice.

Usted se compromete expresamente a hacer un uso adecuado de los contenidos y servicios de EL DULZIA SALAMANCA EVENTOS y a no emplearlos para, entre otros:
- Difundir contenidos, delictivos, violentos, pornográficos, racistas, xenófobo, ofensivos, de apología del terrorismo o, en general, contrarios a la ley o al orden público.
- Introducir en la red virus informáticos o realizar actuaciones susceptibles de alterar, estropear, interrumpir o generar errores o daños en los documentos electrónicos, datos o sistemas físicos y lógicos de DULZIA SALAMANCA EVENTOS o de terceras personas; así como obstaculizar el acceso de otros usuarios al sitio web y a sus servicios mediante el consumo masivo de los recursos informáticos a través de los cuales DULZIA SALAMANCA EVENTOS presta sus servicios.
- Intentar acceder datos de otros personas o a áreas restringidas de los sistemas informáticos de DULZIA SALAMANCA EVENTOS o de terceros y, en su caso, extraer información.
- Vulnerar los derechos de propiedad intelectual o industrial, así como violar la confidencialidad de la información de DULZIA SALAMANCA EVENTOS o de terceros.
- Suplantar la identidad de otro usuario, de las administraciones públicas o de un tercero.
- Reproducir, copiar, distribuir, poner a disposición o de cualquier otra forma comunicar públicamente, transformar o modificar los contenidos, a menos que se cuente con la autorización del titular de los correspondientes derechos o ello resulte legalmente permitido.
- Recabar datos con finalidad publicitaria y de remitir publicidad de cualquier clase y comunicaciones con fines de venta u otras de naturaleza comercial sin que medie su previa solicitud o consentimiento.

Todos los contenidos del sitio web, como textos, fotografías, gráficos, imágenes, iconos, tecnología, software, así como su diseño gráfico y códigos fuente, constituyen una obra cuya propiedad pertenece a DULZIA SALAMANCA EVENTOS, sin que puedan entenderse como cedidos ninguno de los derechos de explotación sobre los mismos más allá de lo estrictamente necesario para el correcto uso de la web.

En definitiva, usted que accede a este sitio web, puede visualizar los contenidos y efectuar, en su caso, copias privadas autorizadas siempre que los elementos reproducidos no sean cedidos posteriormente a terceros, ni se instalen a servidores conectados a redes, ni sean objeto de ningún tipo de explotación.

Asimismo, todas las marcas, nombres comerciales o signos distintivos de cualquier clase que aparecen en el sitio web son propiedad de DULZIA SALAMANCA EVENTOS, sin que pueda entenderse que el uso o acceso al mismo le atribuya derecho alguno sobre los mismos. La distribución, modificación, cesión o comunicación pública de los contenidos y cualquier otro acto que no haya sido expresamente autorizado por el titular de los derechos de explotación quedan prohibidos.

El establecimiento de un hiperenlace no implica en ningún caso la existencia de relaciones entre DULZIA SALAMANCA EVENTOS y el propietario del sitio web en la que se establezca, ni la aceptación y aprobación por parte de DULZIA SALAMANCA EVENTOS de sus contenidos o servicios. Aquellas personas que se propongan establecer un hiperenlace previamente deberán solicitar autorización por escrito a DULZIA SALAMANCA EVENTOS. En todo caso, el hiperenlace únicamente permitirá el acceso a la home-page o página de inicio de nuestro sitio web, asimismo deberá abstenerse de realizar manifestaciones o indicaciones falsas, inexactas o incorrectas sobre ORIGINALIA SALAMANCA CB, o incluir contenidos ilícitos, contrarios a las buenas costumbres y al orden público.

DULZIA SALAMANCA EVENTOS no se responsabiliza del uso que cada usuario le dé a los materiales puestos a disposición en este sitio web ni de las actuaciones que realice en base a los mismos.

4. Exclusión de garantías y de responsabilidad — DULZIA SALAMANCA EVENTOS excluye, hasta donde permite el ordenamiento jurídico, cualquier responsabilidad por los daños y perjuicios de toda naturaleza derivados de:
- La imposibilidad de acceso al sitio web o la falta de veracidad, exactitud, exhaustividad y/o actualidad de los contenidos, así como la existencia de vicios y defectos de toda clase de los contenidos transmitidos, difundidos, almacenados, puestos a disposición a los que se haya accedido a través del sitio web o de los servicios que se ofrecen.
- La presencia de virus o de otros elementos en los contenidos que puedan producir alteraciones en los sistemas informáticos, documentos electrónicos o datos de los usuarios.
- El incumplimiento de las leyes, la buena fe, el orden público, los usos del tráfico y el presente aviso legal como consecuencia del uso incorrecto del sitio web. En particular, y a modo ejemplificativo, DULZIA SALAMANCA EVENTOS no se hace responsable de las actuaciones de terceros que vulneren derechos de propiedad intelectual e industrial, secretos empresariales, derechos al honor, a la intimidad personal y familiar y a la propia imagen, así como la normativa en materia de competencia desleal y publicidad ilícita.

Asimismo, DULZIA SALAMANCA EVENTOS declina cualquier responsabilidad respecto a la información que se halle fuera de esta web y no sea gestionada directamente por nuestro webmaster. La función de los links que aparecen en esta web es exclusivamente la de informar al usuario sobre la existencia de otras fuentes susceptibles de ampliar los contenidos que ofrece este sitio web. DULZIA SALAMANCA EVENTOS no garantiza ni se responsabiliza del funcionamiento o accesibilidad de los sitios enlazados; ni sugiere, invita o recomienda la visita a los mismos, por lo que tampoco será responsable del resultado obtenido. DULZIA SALAMANCA EVENTOSB no se responsabiliza del establecimiento de hipervínculos por parte de terceros.

5. Procedimiento en caso de realización de actividades de carácter ilícito — En el caso de que usted o un tercero considere que existen hechos o circunstancias que revelen el carácter ilícito de la utilización de cualquier contenido y/o de la realización de cualquier actividad en las páginas web incluidas o accesibles a través del sitio web, deberá ponerse en contacto con DULZIA SALAMANCA EVENTOS identificándose debidamente, especificando las supuestas infracciones y declarando expresamente y bajo su responsabilidad que la información proporcionada en la notificación es exacta.

Para toda cuestión litigiosa que incumba al sitio web de DULZIA SALAMANCA EVENTOS, será de aplicación la legislación española, siendo competentes los Juzgados y Tribunales más cercanos a la sede de (España).

6. Publicaciones — La información administrativa facilitada a través del sitio web no sustituye la publicidad legal de las leyes, normativas, planes, disposiciones generales y actos que tengan que ser publicados formalmente a los diarios oficiales de las administraciones públicas, que constituyen el único instrumento que da fe de su autenticidad y contenido. La información disponible en este sitio web debe entenderse como una guía sin propósito de validez legal.

*(El pie de página original muestra textos de plantilla sin personalizar — «BLUSH BOUTIQUE», dirección y teléfono de ejemplo — que se ignoran.)*

---

# 6. Política de privacidad (`/politica-de-privacidad/`)

> Texto literal de la página original.
> **Nota:** en la página nueva (`frontend/src/pages/PoliticaPrivacidadPage.vue`) se han corregido
> estas erratas: «Datos recogido los las cookies» → «Datos recogidos por las cookies» y «mientras
> se mantenga le relación» → «mientras se mantenga la relación».

ACTUALIZACIÓN NUEVA NORMATIVA POLÍTICA DE PRIVACIDAD — Nos ponemos en contacto con usted, como prestadores de servicios que tenemos acceso a sus datos personales, para recordarle que, como responsables del tratamiento de dichos datos, y a raíz de la adaptación al Reglamento (UE) 2016/679 del Parlamento Europeo y del Consejo, de 27 de abril de 2016, relativo a la protección de las personas físicas en lo que respecta al tratamiento de datos personales y a la libre circulación de estos datos, y por el que se deroga la Directiva 95/46/CE, que será aplicable a partir del 25 de mayo de 2018, tratamos sus datos con las finalidades de desarrollo, mantenimiento y fidelización de la relación comercial y contractual correspondiente, y estamos autorizados a conservarlos mientras se mantengan expectativas sobre ellas o durante los años necesarios para cumplir con las obligaciones legales dimanantes de las mismas. Del mismo modo, le informamos que la base jurídica del tratamiento de sus datos, además del interés legítimo, es el consentimiento que expresamente nos ha prestado, así como la ejecución del contrato del que es parte. Le comunicamos también que sus datos personales no se cederán a terceros salvo obligación legal. Asimismo, le informamos de la posibilidad que tiene de ejercer los derechos de acceso, rectificación o supresión, portabilidad, además de la limitación del tratamiento o a oponerse al mismo, así como a retirar en cualquier momento el consentimiento prestado para tratar sus datos y a no ser objeto de una decisión basada únicamente en el tratamiento automatizado, contactando por escrito con nosotros en nuestra dirección postal o electrónica acompañando fotocopia de su documento oficial identificativo. Finalmente, le recordamos su derecho a presentar una reclamación ante la Agencia Española de Protección de Datos si considerara que el tratamiento no es acorde a la mencionada normativa europea.

POLÍTICA DE PRIVACIDAD — El Titular se compromete a cumplir con la normativa en materia de Protección de Datos de carácter personal y a respetar la privacidad de los Usuarios. El objetivo es ofrecer el mejor servicio al Usuario y para ello es necesario contar con sus datos.

Responsable del Tratamiento: DULZIA SALAMANCA EVENTOS
- NIF: 70900291P
- Teléfono: 629991659
- Email: info@dulziasalamancaeventos.com

Finalidades: Servicios para todo tipo de eventos
- Atención de consultas: Atender las consultas del Usuario que se ponga en contacto a través de los Formularios.
- Prestar el servicio solicitado por el Usuario: Tramitación del alta como usuario del Sitio Web con la finalidad de prestarle los servicios que se ofertan.
- Inscripción: Procedimiento para comunicar el Usuario su interés por participar en actividades.
- Datos de navegación: Datos recogido los las cookies propias o de terceros que puedan ser generadas por este Sitio Web y de las que se informa de forma específica en la Política de Cookies.
- Comunicaciones comerciales: Para el Usuario que voluntariamente acepte esta opción, se le remitirán promociones, publicidad, ofertas, noticias, invitación a eventos y cualquier tipo de información comercial del Titular.
- Comunicaciones comerciales con información de terceros: Para el Usuario que voluntariamente acepte esta opción, se le remitirán promociones, publicidad, ofertas, noticias, invitación a eventos y cualquier tipo de información comercial de terceros con los que el Titular tenga un acuerdo, en todo caso será el Titular el que realizará este envío y en ningún caso se cederán los datos del Usuario a éstos terceros.
- Comunicaciones comerciales personalizadas: Para el Usuario que voluntariamente acepte esta opción, se le remitirán promociones, publicidad, ofertas, noticias, invitación a eventos y cualquier tipo de información comercial personalizada que el Titular pueda determinar en función de su comportamiento, preferencias y perfil personal.

En todo caso el Usuario puede posteriormente en cualquier momento oponerse a la recepción de publicidad enviando email a la dirección de email arriba indicada desde la misma cuenta en la que se reciban las comunicaciones.

Legitimación y conservación — Base jurídica del tratamiento: Consentimiento otorgado por el Usuario facilitando sus datos de forma voluntaria y aceptando la Política de Privacidad. En caso de no facilitar los datos necesarios para estas finalidades no será posible prestarle los servicios. Los datos se conservarán mientras se mantenga le relación y no se solicite su supresión y en cualquier caso en cumplimiento de plazos legales de prescripción que le resulten de aplicación.

Cesiones — No se tienen previstas cesiones de los datos recabados a través de este Sitio Web, salvo que expresamente se indique, en su caso, en el texto informativo correspondiente.

Derechos de los interesados — Los Usuarios pueden ejercitar sus derechos de acceso, rectificación, supresión, portabilidad y la limitación u oposición enviando solicitud firmada por correo postal con asunto “Protección de Datos”, a la dirección indicada anteriormente en el apartado Domicilio, indicando claramente los datos de contacto y remitiendo copia de su documento de identidad o enviando solicitud por email con asunto “Ejercicio de Derechos”, a la dirección de email arriba indicada, desde la misma cuenta de email que facilitó. Los Usuarios tienen derecho a retirar el consentimiento prestado y tienen derecho a reclamar ante la Autoridad de Control (Agencia Española de Protección de Datos www.agpd.es).

1. Campos de texto libre — Los campos de texto libre que, a disposición del Usuario, puedan aparecer en los formularios del Sitio Web tienen como única y exclusiva finalidad el recabar información para mejorar la calidad de los Servicios. El Usuario no incluirá, en aquellos espacios que el Sitio Web pueda ofertar como "campos de texto libre", ningún dato de carácter personal que pueda ser calificado dentro de aquellos datos para los que se exige un nivel de protección especial, entendiéndose como tales datos, a título enunciativo y no limitativo, los relativos a situación económico-financiera, perfiles psicológicos, ideología, religión, creencias, afiliación sindical, salud, origen racial y/o vida sexual.

2. Campos no obligatorios — El Titular comunica al Usuario el carácter no obligatorio de la recogida de algunos datos, salvo en los campos que se indique lo contrario mediante un (*). No obstante, la no cumplimentación de dichos datos podrá impedir prestar todos aquellos Servicios vinculados a tales datos, liberándole de toda responsabilidad por la no prestación o prestación incompleta de estos Servicios.

3. Acceso y rectificación de los datos personales — El Usuario se compromete a proporcionar información cierta en relación con sus datos personales, y mantener los datos facilitados al Titular siempre actualizados. El Usuario responderá, en cualquier caso, de la veracidad de los datos facilitados, reservándose el Titular el derecho de excluir de los servicios a todo Usuario que haya facilitado datos falsos, sin perjuicio de las demás acciones que procedan en Derecho. Los datos facilitados por el Usuario se presumirán correctos, por lo que, en caso de envío erróneo de sus datos por parte del Usuario, el Titular, declina cualquier responsabilidad en caso de la incorrecta ejecución o no ejecución del envío, así como el incorrecto cumplimiento de los trámites administrativos necesarios.

4. Comunicación de datos personales — En relación a la gestión de los servicios, los datos de los Usuarios podrán ser tratados por empresas que presten al Titular diversos servicios, entre otros, de envío, mensajería, contabilidad, asesoría, mantenimiento informático, o cualquier otro que por su condición de Encargada del Tratamiento sea indispensable o inevitable que accedan o traten estos datos. Este tratamiento no será considerado en ningún caso una cesión de datos. El Titular no cederá sus datos a terceras personas en ningún caso.

5. Confidencialidad — Además, también tendrá la condición de confidencial la información de cualquier tipo que las partes intercambien entre sí, aquella que éstas acuerden que tiene tal naturaleza, o la que simplemente verse sobre el contenido de dicha información. La visualización de datos a través de Internet, no supondrá el acceso directo a los mismos, salvo consentimiento expreso de su titular para cada ocasión.

6. Fotografías — En cumplimiento de lo establecido en la actual normativa de Protección de Datos de Carácter Personal se informa que las fotografías en las que aparezcan personas tienen consideración de dato de carácter personal. Con esta comunicación se informa que en este Sitio Web pueden aparecer fotografías de personas con motivos de promoción y que voluntariamente han accedido a ello. Si en algún caso, cualquier persona que aparezca en ellas desea que no se muestre alguna fotografía, rogamos se pongan en contacto y se procederá a su retirada a la mayor brevedad posible. No será válido para las personas que han prestado un servicio de forma profesional.

