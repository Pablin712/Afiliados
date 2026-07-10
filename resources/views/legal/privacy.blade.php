<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('messages.link.privacy_policy') }} — {{ config('app.name', 'AET Trader Academy') }}</title>
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

            <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-graphite-100">Política de Privacidad</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-graphite-400">Última actualización: 8 de julio de 2026</p>

            <div class="mt-8 space-y-8 text-sm leading-7 text-gray-700 dark:text-graphite-300">
                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">1. Responsable del tratamiento</h2>
                    <p class="mt-2">
                        AET Trader Academy ("nosotros", "el comercio") es responsable del tratamiento de los datos
                        personales que recopilamos a través de este sitio web (<strong>aettraderacademy.es</strong>).
                        Puedes contactarnos en
                        <a href="mailto:Aetsas01@gmail.com" class="text-brand-600 hover:underline dark:text-brand-400">Aetsas01@gmail.com</a>
                        o al teléfono <a href="https://wa.me/593978855098" class="text-brand-600 hover:underline dark:text-brand-400">+593 97 885 5098</a>.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">2. Datos que recopilamos</h2>
                    <p class="mt-2">Recopilamos los siguientes datos cuando te registras y usas la plataforma:</p>
                    <ul class="mt-2 list-disc list-inside space-y-1">
                        <li>Nombre completo, correo electrónico y número de teléfono.</li>
                        <li>Datos de la cuenta bancaria que tú nos proporciones, únicamente cuando eliges pagar por
                            transferencia bancaria (para validar el comprobante de pago).</li>
                        <li>Identificador interno de tu cuenta y del referido/afiliado que te invitó, si aplica.</li>
                        <li>Datos de uso de la plataforma (cursos vistos, sesiones, dispositivo desde el que accedes).</li>
                    </ul>
                    <p class="mt-3 font-medium text-gray-900 dark:text-graphite-100">
                        No almacenamos números de tarjeta de crédito/débito ni códigos de seguridad (CVV).
                    </p>
                    <p class="mt-2">
                        Los pagos con tarjeta se procesan directamente por nuestra pasarela de pagos certificada
                        <strong>Datafast S.A.</strong>, mediante un formulario seguro (widget) con certificación PCI
                        DSS. Nosotros solo recibimos la confirmación del resultado de la transacción, nunca los
                        datos completos de la tarjeta.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">3. Finalidad del tratamiento</h2>
                    <p class="mt-2">Usamos tus datos para:</p>
                    <ul class="mt-2 list-disc list-inside space-y-1">
                        <li>Crear y administrar tu cuenta y membresía.</li>
                        <li>Procesar pagos (tarjeta vía Datafast o transferencia bancaria) y validar comprobantes.</li>
                        <li>Darte acceso a los cursos, señales y comunidad correspondientes a tu plan.</li>
                        <li>Calcular y pagar comisiones de nuestro programa de referidos/afiliados, si participas en él.</li>
                        <li>Enviarte notificaciones operativas por correo, WhatsApp o Telegram (por ejemplo,
                            confirmación de pago o acceso a un grupo).</li>
                        <li>Cumplir obligaciones legales y prevenir fraude.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">4. Con quién compartimos tus datos</h2>
                    <p class="mt-2">No vendemos tus datos personales. Los compartimos únicamente con:</p>
                    <ul class="mt-2 list-disc list-inside space-y-1">
                        <li><strong>Datafast S.A.</strong>, como pasarela de pagos, para procesar transacciones con tarjeta.</li>
                        <li>Proveedores de mensajería (WhatsApp Business API, Telegram) para notificaciones y acceso a
                            comunidades relacionadas con tu membresía.</li>
                        <li>Autoridades competentes, cuando exista una obligación legal de hacerlo.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">5. Conservación de datos</h2>
                    <p class="mt-2">
                        Conservamos tus datos mientras mantengas una cuenta activa con nosotros y durante el tiempo
                        adicional necesario para cumplir obligaciones legales, contables o fiscales, o para resolver
                        disputas relacionadas con pagos.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">6. Tus derechos</h2>
                    <p class="mt-2">
                        De acuerdo con la Ley Orgánica de Protección de Datos Personales del Ecuador, puedes ejercer
                        en cualquier momento tus derechos de acceso, rectificación, actualización, eliminación,
                        oposición y portabilidad de tus datos, escribiéndonos a
                        <a href="mailto:Aetsas01@gmail.com" class="text-brand-600 hover:underline dark:text-brand-400">Aetsas01@gmail.com</a>.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">7. Seguridad</h2>
                    <p class="mt-2">
                        Aplicamos medidas técnicas y organizativas razonables para proteger tus datos, incluyendo
                        conexión cifrada (TLS 1.2/1.3) en todo el sitio y el uso de una pasarela de pagos certificada
                        PCI DSS para el procesamiento de tarjetas.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">8. Cookies</h2>
                    <p class="mt-2">
                        Usamos cookies de sesión estrictamente necesarias para mantener tu sesión iniciada y recordar
                        tu preferencia de idioma y tema visual. No usamos cookies de publicidad de terceros.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">9. Cambios a esta política</h2>
                    <p class="mt-2">
                        Podemos actualizar esta política ocasionalmente. Publicaremos cualquier cambio en esta misma
                        página junto con la fecha de última actualización.
                    </p>
                </section>
            </div>
        </main>
    </body>
</html>
