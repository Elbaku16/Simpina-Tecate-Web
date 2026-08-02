<?php
require_once __DIR__ . '/../../../back-end/auth/verificar-sesion.php';
require_once __DIR__ . '/../../includes/config.php';
requerir_admin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPINNA | Generar códigos QR</title>
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>global/layout.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>admin/admin.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>admin/generar-qr.css">
</head>

<body>
    <?php require_once __DIR__ . '/../../includes/header-admin.php'; ?>

    <main class="qr-page">
        <section class="qr-page-header" aria-labelledby="qrPageTitle">
            <a href="<?php echo FRAMES_URL; ?>panel/panel-admin.php" class="qr-back">
                <i class="fa-solid fa-angle-left" aria-hidden="true"></i>
                Regresar al Panel
            </a>

            <h1 id="qrPageTitle">Generar códigos QR</h1>
            <p>Genera y descarga el código QR de acceso para cada encuesta.</p>
        </section>

        <section class="qr-generator" aria-labelledby="qrGeneratorTitle">
            <div class="qr-section-header">
                <h2 id="qrGeneratorTitle">Generar código</h2>
                <p>Selecciona el nivel educativo al que corresponde la encuesta.</p>
            </div>

            <form id="qrForm" class="qr-form">
                <div class="qr-field">
                    <label for="nivel">Nivel educativo</label>
                    <select id="nivel" name="nivel" required>
                        <option value="" selected disabled>Selecciona un nivel</option>
                        <option value="preescolar">Preescolar</option>
                        <option value="primaria">Primaria</option>
                        <option value="secundaria">Secundaria</option>
                        <option value="preparatoria">Preparatoria</option>
                    </select>
                </div>

                <button type="submit" id="generateButton" class="qr-button qr-button--primary">
                    Generar código QR
                </button>
            </form>

            <div id="qrError" class="qr-error" role="alert" hidden></div>

            <div id="loading" class="qr-loading" role="status" aria-live="polite" hidden>
                <span class="qr-loading__spinner" aria-hidden="true"></span>
                <span>Generando código QR…</span>
            </div>

            <section id="qrResult" class="qr-result" tabindex="-1" aria-labelledby="nivelTitle" hidden>
                <div class="qr-result-header">
                    <h2 id="nivelTitle"></h2>
                    <p>El código está listo para descargar y compartir.</p>
                </div>

                <div class="qr-result-content">
                    <div class="qr-image-wrapper">
                        <img id="qrImage" src="" alt="">
                    </div>

                    <div class="qr-result-info">
                        <span class="qr-label">Enlace de la encuesta</span>
                        <a id="encuestaUrl" href="#" target="_blank" rel="noopener"></a>

                        <div class="qr-actions">
                            <a id="downloadLink" href="#" download class="qr-button qr-button--primary">
                                <i class="fa-solid fa-download" aria-hidden="true"></i>
                                Descargar QR
                            </a>
                            <a id="openSurveyLink" href="#" target="_blank" rel="noopener" class="qr-button qr-button--secondary">
                                Ver encuesta
                            </a>
                            <button type="button" id="resetQrButton" class="qr-button qr-button--secondary">
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    </main>

    <?php require_once __DIR__ . '/../../includes/footer-admin.php'; ?>

    <script>
        const qrForm = document.getElementById('qrForm');
        const nivelSelect = document.getElementById('nivel');
        const generateButton = document.getElementById('generateButton');
        const loading = document.getElementById('loading');
        const qrResult = document.getElementById('qrResult');
        const qrError = document.getElementById('qrError');
        const qrImage = document.getElementById('qrImage');
        const nivelTitle = document.getElementById('nivelTitle');
        const encuestaUrl = document.getElementById('encuestaUrl');
        const downloadLink = document.getElementById('downloadLink');
        const openSurveyLink = document.getElementById('openSurveyLink');

        qrForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const nivel = nivelSelect.value;
            if (!nivel) {
                nivelSelect.focus();
                return;
            }

            qrError.hidden = true;
            qrResult.hidden = true;
            loading.hidden = false;
            generateButton.disabled = true;
            generateButton.textContent = 'Generando…';

            try {
                const response = await fetch(`${window.BASE_URL}/back-end/routes/qr/generar.php?nivel=${encodeURIComponent(nivel)}`);
                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'No fue posible generar el código QR.');
                }

                nivelTitle.textContent = `Encuesta de ${data.nivel}`;
                qrImage.src = data.qr_url;
                qrImage.alt = `Código QR para la encuesta de ${data.nivel}`;
                encuestaUrl.textContent = data.encuesta_url;
                encuestaUrl.href = data.encuesta_url;
                downloadLink.href = data.qr_url;
                downloadLink.download = `QR-${nivel}-SIMPINNA.png`;
                openSurveyLink.href = data.encuesta_url;

                qrResult.hidden = false;
                qrResult.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } catch (error) {
                qrError.textContent = error.message;
                qrError.hidden = false;
            } finally {
                loading.hidden = true;
                generateButton.disabled = false;
                generateButton.textContent = 'Generar código QR';
            }
        });

        document.getElementById('resetQrButton').addEventListener('click', () => {
            qrForm.reset();
            qrResult.hidden = true;
            qrError.hidden = true;
            qrImage.src = '';
            qrImage.alt = '';
            nivelSelect.focus();
            document.querySelector('.qr-generator').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    </script>
</body>
</html>
