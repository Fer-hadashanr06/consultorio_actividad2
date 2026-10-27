@extends('layouts.publico')
@section('titulo', 'Consultorio odontológico')

@section('contenido')
    <section class="portada">
        <div class="contenedor">
            <div>
                <h1>Cuidar tu boca es cuidar tu salud</h1>
                <p class="lead">La odontología previene, diagnostica y trata las enfermedades de los dientes, las encías y toda la boca. Aquí encuentras lo básico para entenderla y un equipo para atenderte.</p>
                <div class="botones">
                    @auth
                        <a class="boton grande" href="{{ route('citas.create') }}">Agendar una cita</a>
                        <a class="boton grande contorno" href="{{ route('panel') }}">Ir al panel</a>
                    @else
                        <a class="boton grande" href="{{ route('login') }}">Iniciar sesión</a>
                        <a class="boton grande contorno" href="#que-es">Conocer más</a>
                    @endauth
                </div>
            </div>
            <div class="arco">
                <svg id="arcada" viewBox="0 25 640 230" role="group" aria-label="Arcada dental superior vista desde abajo; cada diente es interactivo"></svg>
                <div class="pie" id="arcada-pie" aria-live="polite">
                    <strong>Toca un diente</strong>
                    <span>Cada tipo de diente tiene una función distinta al masticar.</span>
                </div>
                <p class="nota">Dibujo esquemático de la mitad superior de la boca de un adulto.</p>
            </div>
        </div>
    </section>

    <div class="contenedor">
        <section class="seccion" id="que-es">
            <h2>Qué estudia la odontología</h2>
            <p class="sub">Es la rama de la salud que se ocupa de los dientes, las encías, la lengua, el paladar y las articulaciones de la mandíbula. Su primer objetivo es evitar que los problemas aparezcan.</p>
            <div class="dos-col">
                <div class="diente-capas">
                    <h3 style="margin-top:0">Las capas de un diente</h3>
                    <dl>
                        <div><dt><i style="background:#fff"></i> Esmalte</dt><dd>La capa externa y el tejido más duro del cuerpo. Se desgasta con ácidos y azúcares, y no se regenera.</dd></div>
                        <div><dt><i style="background:#f1dfb0"></i> Dentina</dt><dd>Está bajo el esmalte. Cuando queda expuesta aparece la sensibilidad al frío o al calor.</dd></div>
                        <div><dt><i style="background:#e8a1a1"></i> Pulpa</dt><dd>El centro vivo del diente, con nervios y vasos sanguíneos. Si se infecta duele y puede necesitar endodoncia.</dd></div>
                        <div><dt><i style="background:#c9d6df"></i> Raíz y encía</dt><dd>La raíz sostiene el diente en el hueso; la encía sana la rodea y la protege.</dd></div>
                    </dl>
                </div>
                <div>
                    <h3 style="margin-top:0">Algunos datos útiles</h3>
                    <p>Un adulto tiene hasta 32 dientes definitivos, contando las cuatro muelas del juicio. Los niños tienen 20 dientes de leche, que van cayendo entre los 6 y los 12 años aproximadamente.</p>
                    <p>Las caries y la enfermedad de las encías son las dos causas más comunes de pérdida dental. Las dos se pueden prevenir con higiene diaria y controles periódicos.</p>
                    <div class="datos">
                        <div><b>32</b><span>dientes definitivos en un adulto</span></div>
                        <div><b>20</b><span>dientes de leche en un niño</span></div>
                        <div><b>2 min</b><span>de cepillado, dos veces al día</span></div>
                        <div><b>6 meses</b><span>entre controles, salvo otra indicación</span></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="seccion" id="especialidades">
            <h2>Especialidades del consultorio</h2>
            <p class="sub">Cada especialidad trata un problema distinto. Si no sabes cuál necesitas, en la primera consulta el odontólogo te orienta.</p>
            <div class="servicios">
                @foreach ($especialidades as $clave => $esp)
                    <article class="servicio {{ $clave }}">
                        <h3>{{ $esp['nombre'] }}</h3>
                        <p>{{ $esp['descripcion'] }}</p>
                        <ul>
                            @foreach ($esp['casos'] as $caso)
                                <li>{{ $caso }}</li>
                            @endforeach
                        </ul>
                        @php($equipo = $activos->get($clave, collect()))
                        <small>
                            @if ($equipo->isEmpty())
                                Sin odontólogos disponibles por ahora
                            @else
                                Equipo: {{ $equipo->map(fn ($o) => 'Dr(a). ' . $o->apellido)->implode(', ') }}
                            @endif
                        </small>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="seccion" id="cuidado">
            <h2>Cuidado diario en casa</h2>
            <p class="sub">La mayor parte de la salud bucal se decide en casa, con pocos hábitos repetidos todos los días.</p>
            <div class="habitos">
                <div class="habito"><b>2×</b><h3>Cepíllate dos veces al día</h3><p>Durante dos minutos, con pasta con flúor y un cepillo de cerdas suaves. La última vez, antes de dormir.</p></div>
                <div class="habito"><b>1×</b><h3>Usa hilo dental</h3><p>Una vez al día. Limpia entre los dientes, donde el cepillo no llega y se acumula la placa.</p></div>
                <div class="habito"><b>3</b><h3>Cambia el cepillo cada tres meses</h3><p>O antes, si las cerdas se abren. Cámbialo también después de un resfrío fuerte.</p></div>
                <div class="habito"><b>↓</b><h3>Reduce los azúcares entre comidas</h3><p>Lo que pesa es la frecuencia: picar dulces o tomar gaseosa a sorbos mantiene el ácido sobre el esmalte.</p></div>
            </div>
            <div class="alerta-senales">
                <h3>Pide una cita pronto si notas</h3>
                <ul>
                    <li>Dolor de muela que no se va o que te despierta de noche</li>
                    <li>Encías que sangran al cepillarte o están inflamadas</li>
                    <li>Sensibilidad intensa al frío, al calor o al dulce</li>
                    <li>Un diente flojo, roto o movido después de un golpe</li>
                    <li>Mal aliento que no mejora con la higiene</li>
                    <li>Hinchazón en la cara o en la encía, con o sin fiebre</li>
                </ul>
            </div>
        </section>

        <section class="seccion" id="preguntas">
            <h2>Preguntas frecuentes</h2>
            <p class="sub">Lo que más nos preguntan antes de la primera cita.</p>
            <div class="faq">
                <details><summary>¿Cada cuánto debo ir al odontólogo?</summary><p>Como regla general, una vez cada seis meses para una revisión y limpieza. Si tienes ortodoncia, encías delicadas o muchas caries, tu odontólogo puede pedirte controles más seguidos.</p></details>
                <details><summary>¿Desde qué edad debe ir un niño?</summary><p>Se recomienda una primera visita cuando sale el primer diente o alrededor del año de edad. Así el niño se acostumbra y el odontopediatra puede orientar a la familia sobre higiene y alimentación.</p></details>
                <details><summary>¿Duele una endodoncia?</summary><p>Se hace con anestesia local, y la sensación durante el procedimiento suele ser parecida a la de una obturación. El dolor fuerte casi siempre viene antes, por la infección de la pulpa, y el tratamiento sirve justamente para quitarlo.</p></details>
                <details><summary>¿Cuánto dura una cita?</summary><p>Las citas se agendan en bloques de 30 minutos, de uno hasta cuatro bloques (30 minutos a 2 horas), según el tratamiento. El odontólogo indica cuántos bloques necesitas.</p></details>
                <details><summary>¿Cuánto tiempo se usan los brackets?</summary><p>Depende de cada caso. Un tratamiento de ortodoncia suele durar entre uno y tres años, con controles mensuales, y después se usa un retenedor para mantener el resultado.</p></details>
                <details><summary>¿Cómo agendo una cita aquí?</summary><p>Las citas las registra el personal del consultorio desde el panel: inicia sesión, elige al odontólogo y la fecha, y selecciona uno de los bloques libres.</p></details>
            </div>
        </section>

        <section class="llamado">
            <div>
                <h2>Personal del consultorio</h2>
                <p>Inicia sesión para registrar citas, consultar pacientes y dar de alta a nuevos odontólogos.</p>
            </div>
            @auth
                <a class="boton grande" href="{{ route('panel') }}">Ir al panel</a>
            @else
                <a class="boton grande" href="{{ route('login') }}">Iniciar sesión</a>
            @endauth
        </section>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var tipos = {
        incisivo_central: ['Incisivo central', 'incisivo', 'Corta los alimentos. Son los dientes de más adelante y los que más se ven al sonreír.', 46, 44],
        incisivo_lateral: ['Incisivo lateral', 'incisivo', 'Ayuda a cortar y a guiar la mordida. Es un poco más pequeño que el central.', 38, 38],
        canino: ['Canino', 'canino', 'Desgarra los alimentos. Tiene la raíz más larga y es clave para la forma de la mordida.', 38, 44],
        premolar: ['Premolar', 'premolar', 'Desgarra y empieza a triturar. Tiene dos cúspides y está entre el canino y la muela.', 44, 42],
        molar: ['Molar', 'molar', 'Tritura y muele. Es el diente más grande y tiene varias raíces, por eso las caries aquí se limpian con más dificultad.', 60, 56]
    };
    var orden = ['molar', 'premolar', 'canino', 'incisivo_lateral', 'incisivo_central'];
    var angulos = [72, 56, 40, 24, 8];
    var svg = document.getElementById('arcada');
    var pie = document.getElementById('arcada-pie');
    var NS = 'http://www.w3.org/2000/svg';
    var seleccionado = null;

    function dibujar(clave, ang) {
        var t = tipos[clave];
        var rad = ang * Math.PI / 180;
        var x = 320 + 245 * Math.sin(rad);
        var y = 62 + 190 * (1 - Math.cos(rad));
        var g = document.createElementNS(NS, 'g');
        g.setAttribute('class', 'diente ' + t[1]);
        g.setAttribute('tabindex', '0');
        g.setAttribute('role', 'button');
        g.setAttribute('aria-label', t[0]);
        g.setAttribute('transform', 'translate(' + x.toFixed(1) + ' ' + y.toFixed(1) + ') rotate(' + ang + ')');
        var r = document.createElementNS(NS, 'rect');
        r.setAttribute('x', -t[3] / 2); r.setAttribute('y', -t[4] / 2);
        r.setAttribute('width', t[3]); r.setAttribute('height', t[4]);
        r.setAttribute('rx', t[1] === 'molar' ? 14 : 12);
        g.appendChild(r);
        if (t[1] === 'molar' || t[1] === 'premolar') {
            var c = document.createElementNS(NS, 'path');
            var w = t[3] / 4, h = t[4] / 4;
            c.setAttribute('d', 'M' + (-w) + ' ' + (-h) + ' L' + w + ' ' + h + ' M' + w + ' ' + (-h) + ' L' + (-w) + ' ' + h);
            c.setAttribute('stroke', '#b3c4d1'); c.setAttribute('stroke-width', '2'); c.setAttribute('stroke-linecap', 'round');
            g.appendChild(c);
        }
        function elegir() {
            if (seleccionado) seleccionado.classList.remove('sel');
            g.classList.add('sel'); seleccionado = g;
            pie.innerHTML = '<strong></strong><span></span>';
            pie.firstChild.textContent = t[0];
            pie.lastChild.textContent = t[2];
        }
        g.addEventListener('click', elegir);
        g.addEventListener('mouseenter', elegir);
        g.addEventListener('focus', elegir);
        g.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); elegir(); } });
        svg.appendChild(g);
    }
    orden.forEach(function (clave, i) {
        dibujar(clave, angulos[i]);
        dibujar(clave, -angulos[i]);
    });
})();
</script>
@endpush
