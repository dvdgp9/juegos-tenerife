<?php

declare(strict_types=1);

namespace JuegosTenerife\Content;

final class ModalityContent
{
    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $slug): ?array
    {
        return self::items()[$slug] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function items(): array
    {
        return [
            'lucha-canaria' => [
                'name' => 'Lucha Canaria',
                'image' => '/assets/images/modalidades/lucha-canaria.jpg',
                'image_alt' => 'Dos jóvenes practicando lucha canaria en un terrero',
                'lead' => 'Definida como el deporte vernáculo de Canarias, la lucha canaria se caracteriza por su habilidad y nobleza. Su principio básico es desequilibrar al contrario hasta hacerle tocar el suelo con cualquier parte del cuerpo que no sea la planta del pie, empleando para ello luchas, mañas o técnicas.',
                'sections' => [
                    [
                        'title' => 'Origen y evolución',
                        'paragraphs' => [
                            'Los continuos episodios de violencia que afectaban a la vida de los canarios prehispánicos, como asaltos piráticos, razias de esclavos, combates entre clanes y la resistencia a la conquista europea, propiciaron el desarrollo de métodos y armas de combate, entre ellos la lucha.',
                            'La lucha sin armas era una de las actividades bélicas que ponían de manifiesto las extraordinarias cualidades físicas de los indígenas canarios, que se hicieron famosas entre los conquistadores.',
                            'A principios del siglo XX aún no existían espacios específicos para los encuentros, que se desarrollaban en cualquier lugar que reuniera unas condiciones mínimas. Los antiguos luchadores se untaban en manteca y ya se intuían algunas técnicas de lucha en el poema de Viana, publicado en Sevilla en 1604.',
                            'Antes de la etapa actual, en Tenerife no se utilizaba el término equipo, sino partido. La forma de agarre también era diferente en cada isla, siendo la mano abajo la empleada en Tenerife.',
                        ],
                    ],
                    [
                        'title' => 'La agarrada y sus técnicas',
                        'paragraphs' => [
                            'No se permite continuar la lucha en el suelo, ni realizar llaves o estrangulamientos. Cada lucha, técnica o maña tiene un desarrollo estructurado. Entre ellas destacan el toque por dentro, la cogida de muslo, el toque para atrás, la cadera, la atravesada, el garabato, el traspió y la levantada.',
                            'La elección de la técnica depende de aspectos como la estatura y el peso del contrario, sus luchas preferidas, aquellas con las que suele caer con facilidad, si es luchador de ataque o contrista y otras características anatómicas relevantes.',
                            'La agarrada es el periodo de tiempo del que disponen los luchadores y luchadoras para imponerse al contrario. Antes de iniciarla se estrechan la mano en el terrero y saludan primero al árbitro.',
                            'El inicio se realiza frente a frente, con la pierna derecha ligeramente adelantada. Cada participante introduce la mano izquierda en el remango exterior delantero del pantalón de la pierna derecha del adversario. Ambos inclinan el cuerpo hasta establecer contacto con los hombros derechos a la misma altura y llevan sus manos derechas juntas hacia el suelo hasta tocarlo con la punta de los dedos, esperando el silbido del árbitro.',
                        ],
                    ],
                    [
                        'title' => 'Sistemas de lucha',
                        'paragraphs' => ['Existen diferentes sistemas de competición:'],
                        'items' => [
                            '<strong>Lucha corrida:</strong> equipos de 18 participantes que se enfrentan en una sola agarrada de tres minutos. Quien vence debe seguir luchando y no puede retirarse hasta ganar tres agarradas o un múltiplo de tres.',
                            '<strong>De tres, las dos mejores:</strong> equipos de 12 participantes. Gana quien suma el mayor número de victorias en agarradas de un minuto y medio.',
                            '<strong>Todos contra todos:</strong> equipos de seis u ocho participantes y dos suplentes. Cada luchador o luchadora se enfrenta a todo el equipo contrario en una agarrada de un minuto y medio y suma un punto por cada rival tumbado. Vence el equipo con más puntos.',
                        ],
                    ],
                ],
            ],
            'juego-del-palo' => [
                'name' => 'Juego del Palo',
                'image' => '/assets/images/modalidades/juego-del-palo.jpg',
                'image_alt' => 'Dos jóvenes practicando juego del palo canario',
                'lead' => 'El juego del palo, también llamado juego del palo canario o palo canario, consiste en un enfrentamiento físico de carácter lúdico entre dos personas que utilizan bastones de madera para simular y recrear un combate real, limitado por una serie de reglas pactadas previamente.',
                'sections' => [
                    [
                        'title' => 'Origen',
                        'paragraphs' => [
                            'Los aborígenes canarios utilizaban el palo como herramienta natural y también para defenderse o atacar. La práctica evolucionó mediante el entrenamiento y la enseñanza hasta adquirir manifestaciones culturales y lúdicas.',
                        ],
                    ],
                    [
                        'title' => 'Tipos, control y agarre',
                        'paragraphs' => [
                            'Existen tres tipos básicos: palo grande, mayor que la altura de quien juega; palo medio, menor que su altura pero no más corto que la cintura; y palo chico, inferior a la altura de la cintura.',
                            'El control del palo es fundamental, tanto para no dañar al contrario como para marcar golpes efectivos. Se puede retener el ataque, tiro o mandado para colocarlo lo más cerca posible del otro jugador, o pasar el palo desviando su trayectoria en el último momento.',
                            'Los modos de agarre son cerrado y abierto, siempre con los pulgares hacia el mismo lado y las palmas enfrentadas.',
                        ],
                        'items' => [
                            '<strong>Agarre a palo largo:</strong> empleado generalmente para el juego a larga distancia.',
                            '<strong>Agarre de punta:</strong> por uno de los extremos del palo.',
                            '<strong>Agarre a palo corto:</strong> utilizado cuando hay poca distancia entre las dos personas.',
                            '<strong>Agarre de punta y media:</strong> una mano en un extremo y la otra cerca del centro.',
                            '<strong>Agarre centrado:</strong> una o dos manos en el centro.',
                        ],
                    ],
                    [
                        'title' => 'Posiciones y estilos',
                        'paragraphs' => [
                            'El juego se desarrolla libremente a partir de un número limitado de posiciones, definidas según las adoptadas por el oponente. Se conocen como cuadras o escuadras y dependen de la situación del pie más adelantado de cada participante respecto a su contrario.',
                            'Según la altura se distingue entre juego erguido o derecho y juego agachado o enguruñado. Por el eje habitual de aplicación de las técnicas se habla de juego alto o juego bajo o rastrero.',
                            'El hombre bueno es la figura mediadora que vela por que el juego se desarrolle correctamente. Se reconocen nueve estilos tradicionales; en Tenerife destacan los estilos Déniz, Morales, Verga y Acosta.',
                        ],
                    ],
                ],
            ],
            'arrastre-canario' => [
                'name' => 'Arrastre Canario',
                'image' => '/assets/images/modalidades/arrastre-canario.jpg',
                'image_alt' => 'Yunta participando en una prueba de arrastre canario',
                'lead' => 'El arrastre canario es un deporte de exhibición y competición en el que una yunta, formada por una pareja de vacas o toros, debe arrastrar una estructura de madera llamada corza, cargada de sacos de arena, a lo largo de 35 metros en el menor tiempo posible.',
                'sections' => [
                    [
                        'title' => 'De la actividad agrícola a la competición',
                        'paragraphs' => [
                            'Nace directamente de las tareas agrícolas tradicionales de las medianías tinerfeñas, donde el ganado era el motor de carga. Tenerife ha impulsado la reglamentación moderna de este deporte: las pruebas ligadas a las fiestas de San Benito Abad, en La Laguna, transformaron esta labor agropecuaria en una competición reglada de alto rendimiento animal y técnico.',
                            'La disciplina premia la complicidad entre el guayero, que conduce la yunta, y sus animales. Cualquier forma de maltrato físico al ganado está estrictamente penalizada.',
                        ],
                    ],
                ],
            ],
            'salto-del-pastor' => [
                'name' => 'Salto del Pastor',
                'image' => '/assets/images/modalidades/salto-del-pastor.jpg',
                'image_alt' => 'Practicante realizando un salto del pastor en un terreno escarpado',
                'lead' => 'El salto o brinco canario emplea una herramienta propia de cabreros y pastores, una lanza o garrote provisto de una punta metálica llamada regatón, para caminar y desplazarse por terrenos irregulares con fuertes pendientes y desniveles.',
                'sections' => [
                    [
                        'title' => 'Herencia y territorio',
                        'paragraphs' => [
                            'Su origen en Tenerife se remonta a la herencia directa de los pastores prehispánicos y de los posteriores cabreros de la isla. La abrupta orografía tinerfeña, marcada por los macizos de Anaga y Teno y las laderas del Teide, exigió el desarrollo de técnicas de salto muy depuradas, como el salto a regatón muerto.',
                            'Actualmente, los jurados, nombre que reciben estos colectivos, preservan la práctica como actividad lúdica y cultural. El salto del pastor representa una unión singular entre el ser humano, la herramienta y el conocimiento milenario del accidentado relieve isleño.',
                        ],
                    ],
                ],
            ],
            'bola-canaria' => [
                'name' => 'Bola Canaria',
                'image' => '/assets/images/modalidades/bola-canaria.jpg',
                'image_alt' => 'Jóvenes jugando a la bola canaria',
                'lead' => 'La bola canaria es un juego de precisión que consiste en lanzar un número determinado de bolas de piedra, pasta o madera desde una línea de salida, con el objetivo de situarlas lo más cerca posible de una bola más pequeña llamada boliche.',
                'sections' => [
                    [
                        'title' => 'Llegada y desarrollo en Tenerife',
                        'paragraphs' => [
                            'Llegó a Tenerife a principios del siglo XX a través de emigrantes de Lanzarote. Comenzó a practicarse en los barrios de Valleseco y La Candelaria, en La Cuesta, durante los años veinte, y posteriormente en la calle Castro de Santa Cruz durante las décadas de 1960 y 1970.',
                            'Aunque existen juegos parecidos, como la petanca francesa, las bochas practicadas en la península y Europa, la bola rafa de Italia, Polonia, Rusia o Argentina y la bola criolla de Venezuela, la bola canaria presenta peculiaridades propias que la diferencian.',
                            'En Tenerife, especialmente en determinadas zonas del norte, el juego recibió cierta influencia de la forma de jugar venezolana y de los emigrantes canario-venezolanos.',
                        ],
                    ],
                    [
                        'title' => 'Equipos y especialistas',
                        'paragraphs' => [
                            'Cada equipo tiene cuatro componentes y cada uno lanza tres bolas. Antes de iniciar el juego se sortea el boliche para decidir qué equipo empieza; normalmente, el equipo que pierde el sorteo elige color.',
                            'Como ocurre en otros deportes, dentro de cada equipo existen diferentes especialistas:',
                        ],
                        'items' => [
                            '<strong>Arrimador:</strong> interviene cuando la jugada es de arrime.',
                            '<strong>Medio:</strong> suele desempeñar todo tipo de jugadas.',
                            '<strong>Marranero o bochador de arrastre:</strong> destaca por su efectividad.',
                            '<strong>Bochador de bola tapada:</strong> juega a todas las distancias y ocupa el puesto más difícil del equipo.',
                        ],
                    ],
                ],
            ],
            'lucha-del-garrote' => [
                'name' => 'Lucha del Garrote',
                'image' => '/assets/images/modalidades/lucha-del-garrote.jpg',
                'image_alt' => 'Dos practicantes realizando una demostración de lucha del garrote',
                'lead' => 'La lucha del garrote es un sistema de combate y defensa personal que utiliza un bastón grueso y robusto, llamado garrote o lata, que solía alcanzar la altura del hombro o la cabeza del pastor. Se emplea con ambas manos para realizar golpes, enganches, proyecciones y luxaciones.',
                'sections' => [
                    [
                        'title' => 'Origen y características',
                        'paragraphs' => [
                            'Los juegos con palos grandes surgieron para cubrir las necesidades de las peleas reales, normalmente esporádicas. También permitían a los pastores, principales usuarios de este utensilio grande y pesado, echarse unas puntas y entretenerse en el campo.',
                            'Debido a su tamaño y grosor, el garrote resulta difícil de sujetar y utilizar por un solo extremo, por lo que favorece un agarre más centrado.',
                        ],
                    ],
                    [
                        'title' => 'Técnicas de defensa y ataque',
                        'items' => [
                            '<strong>Defensa:</strong> se basa en tres tipos de paradas: alta, lateral, que es la más habitual, y reforzada, utilizada ante ataques laterales muy bajos o especialmente fuertes.',
                            '<strong>Ataque:</strong> incluye golpes a la cabeza, al cuadril o las costillas, a la corva o los tobillos y a la entrepierna, además del finchón o finchada, la revoleada alta o baja, la cogotera y la sobaquera, entre otras técnicas.',
                        ],
                    ],
                ],
            ],
        ];
    }
}
