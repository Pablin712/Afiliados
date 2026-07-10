<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('messages.link.terms_and_conditions') }} — {{ config('app.name', 'AET Trader Academy') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('storage/siglas2.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            (() => {
                const savedTheme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const useDark = savedTheme ? savedTheme === 'dark' : prefersDark;
                document.documentElement.classList.toggle('dark', useDark);
            })();
        </script>
    </head>
    <body class="font-sans bg-gray-100 text-gray-900 dark:bg-graphite-950 dark:text-graphite-100 antialiased">
        <header class="border-b border-gray-200 bg-white dark:bg-graphite-900 dark:border-graphite-800">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center gap-3">
                <a href="/" class="flex items-center gap-3">
                    <x-application-logo class="h-9 w-auto" src="{{ asset('storage/siglas2.png') }}" />
                    <span class="text-sm font-semibold text-gray-800 dark:text-graphite-100">AET Trader Academy</span>
                </a>
            </div>
        </header>

        <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
            <a href="/" class="text-sm text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">&larr; Volver al inicio</a>

            <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-graphite-100">Términos y Condiciones</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-graphite-400">Última actualización: 8 de julio de 2026</p>

            <div class="mt-8 space-y-8 text-sm leading-7 text-gray-700 dark:text-graphite-300">
                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">1. Aceptación de los términos</h2>
                    <p class="mt-2">
                        Al registrarte y usar la plataforma de AET Trader Academy (<strong>aettraderacademy.es</strong>),
                        aceptas estos Términos y Condiciones en su totalidad. Si no estás de acuerdo, no debes usar
                        el sitio ni adquirir ninguno de nuestros planes.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">2. Descripción del servicio</h2>
                    <p class="mt-2">
                        Ofrecemos acceso a cursos y contenido educativo sobre trading, comunidad y programas de
                        formación, mediante membresías de pago. El acceso al contenido depende del plan contratado y
                        de que el pago correspondiente haya sido aprobado.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">3. Registro de cuenta</h2>
                    <p class="mt-2">
                        Debes proporcionar información veraz y mantenerla actualizada. Eres responsable de la
                        confidencialidad de tus credenciales de acceso y de toda actividad realizada desde tu cuenta.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">4. Planes, precios y medios de pago</h2>
                    <p class="mt-2">Ofrecemos dos medios de pago:</p>
                    <ul class="mt-2 list-disc list-inside space-y-1">
                        <li><strong>Tarjeta de crédito/débito</strong>, procesada de forma segura por nuestra pasarela
                            de pagos certificada Datafast S.A. La aprobación es automática cuando el banco emisor
                            autoriza la transacción.</li>
                        <li><strong>Transferencia bancaria</strong>, sujeta a revisión y aprobación manual por nuestro
                            equipo antes de activar el acceso.</li>
                    </ul>
                    <p class="mt-2">
                        Los precios se muestran en dólares de los Estados Unidos (USD) y pueden variar sin previo
                        aviso para nuevas contrataciones; los cambios de precio no afectan membresías ya pagadas.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">5. Renovaciones</h2>
                    <p class="mt-2">
                        Las membresías tienen una vigencia definida y deben renovarse manualmente al vencer, mediante
                        un nuevo pago con tarjeta o transferencia. No realizamos cobros recurrentes automáticos.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">6. Cancelaciones y reembolsos</h2>
                    <p class="mt-2">
                        Las solicitudes de cancelación o reembolso se gestionan de forma administrativa, caso por
                        caso, escribiendo a
                        <a href="mailto:Aetsas01@gmail.com" class="text-brand-600 hover:underline dark:text-brand-400">Aetsas01@gmail.com</a>.
                        No ofrecemos anulación automática desde la plataforma; toda anulación de una transacción con
                        tarjeta se coordina directamente con Datafast y la entidad bancaria correspondiente.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">7. Programa de referidos / afiliados</h2>
                    <p class="mt-2">
                        Si participas en nuestro programa de referidos, las comisiones se calculan y pagan conforme a
                        las reglas vigentes de la plataforma en el momento de cada transacción, y pueden ser
                        modificadas hacia adelante sin afectar comisiones ya generadas.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">8. Propiedad intelectual</h2>
                    <p class="mt-2">
                        Todo el contenido de los cursos, materiales, marcas y logotipos son propiedad de AET Trader
                        Academy o de sus licenciantes. Su membresía te otorga una licencia personal, intransferible y
                        no exclusiva para uso educativo, quedando prohibida su reproducción o redistribución sin
                        autorización expresa.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">9. Exoneración de responsabilidad sobre resultados de trading</h2>
                    <p class="mt-2">
                        El contenido educativo, señales o herramientas que ofrecemos tienen fines exclusivamente
                        formativos e informativos y <strong>no constituyen asesoría financiera, de inversión ni
                        garantía de resultados</strong>. Operar en los mercados financieros implica riesgo de pérdida
                        de capital. Cada usuario es responsable de sus propias decisiones de inversión.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">10. Modificaciones</h2>
                    <p class="mt-2">
                        Podemos actualizar estos Términos y Condiciones en cualquier momento. Los cambios se
                        publicarán en esta página junto con la fecha de última actualización y aplicarán a partir de
                        su publicación.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">11. Ley aplicable</h2>
                    <p class="mt-2">
                        Estos términos se rigen por las leyes de la República del Ecuador. Cualquier controversia se
                        someterá a los jueces competentes de Ecuador.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">12. Contacto</h2>
                    <p class="mt-2">
                        Para consultas sobre estos términos, escríbenos a
                        <a href="mailto:Aetsas01@gmail.com" class="text-brand-600 hover:underline dark:text-brand-400">Aetsas01@gmail.com</a>
                        o al <a href="https://wa.me/593978855098" class="text-brand-600 hover:underline dark:text-brand-400">+593 97 885 5098</a>.
                    </p>
                </section>
            </div>
        </main>
    </body>
</html>
