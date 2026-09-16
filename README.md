# Veterinaria 5 de Abril - Despliegue

## Requisitos
- PHP 8+ (mysqli activado)
- MySQL/MariaDB
- Hosting con soporte PHP (cPanel, CPanel/LiteSpeed, etc.) — Hostinger recomendado (PHP+MySQL+SSL, dominio .cl gratis 1er año)

## Archivos del proyecto (C:\xampp\htdocs\vet5abril)
- `index.php` — sitio público (catálogo de servicios)
- `landing.php` — landing de conversión para Google Ads (anclas: atención/vacunas/castración + WhatsApp)
- `ads/` — estructura completa de campaña Google Ads (`campana_estructura.md`) + estimación (`estimacion.md`)
- `admin/` — panel de administración (login, dashboard, servicios, categorías, testimonios, usuarios, consultas, historial del asistente IA)
- `chat/` — asistente virtual (widget + endpoint). Con `AI_API_KEY` usa Google Gemini; sin clave responde FAQ automático
- `css/style.css` — estilos
- `sql/setup.sql` — esquema + datos de ejemplo
- `tools/fix_acentos.php` — corrige acentos (ya ejecutado una vez)
- `uploads/` — imágenes de servicios y categorías

## Configuración
Editar `config.php`:
- `DB_NAME`, `DB_USER`, `DB_PASS` según el hosting
- `SITE_URL` (ej: `https://mivida.cl`)
- `WHATSAPP_NUMBER` (número real, formato `569XXXXXXXX`)
- `ADDRESS` / `ADDRESS_SHORT` (dirección del local, se usa en landing y footer)
- `GA_MEASUREMENT_ID` — el `G-XXXX` de Google Ads; poner `''` para desactivar el tag
- `AI_API_KEY` / `AI_MODEL` — asistente virtual. Vacío = modo FAQ gratuito. Para IA real crea una clave gratis en https://aistudio.google.com (Gemini, modelo `gemini-2.0-flash`) y pégala. Las conversaciones se guardan en `admin/chat_logs.php`

## Importar la base de datos (IMPORTANTE: respetar UTF-8)

Desde la terminal MySQL o phpMyAdmin usa:

```
mysql --default-character-set=utf8mb4 -u USUARIO -p DBNAME < setup.sql
```

Si usas phpMyAdmin, importa el archivo `setup.sql` directamente (lo maneja como UTF-8 correctamente).

NO importes el SQL desde PowerShell con `Get-Content | mysql`:
re-codifica a ANSI y rompe las tildes/ñ (ya nos pasó una vez).

## Usuarios iniciales (contraseñas seguras — guardadas en local, NO subir a hosting)
- admin@vet5abril.cl (rol admin - "Mamá")
- owner@vet5abril.cl (rol editor - "Propietario")

Las contraseñas reales están en `C:\Users\X1\AppData\Local\Temp\opencode\credenciales_vet5abril.txt`
(no se suben al hosting). El SQL (`sql/setup.sql`) contiene los hashes bcrypt ya generados.
Para cambiar contraseñas usa el panel o PHP: `password_hash('nueva', PASSWORD_BCRYPT)`.

## Subir archivos
- Sube el contenido completo de la carpeta del proyecto a `public_html/` o `www/` del hosting.
- **NO subir** `credenciales*.txt`, `*.md` internos si no los necesitas en producción, ni el archivo de credenciales.
- Asegúrate de que `uploads/services` tenga permisos de escritura (755 o 775).

## Google Ads
1. Publicar el sitio con HTTPS y dominio.
2. Crear cuenta de Google Ads en ads.google.com (correo + tarjeta).
3. Poner `GA_MEASUREMENT_ID` en `config.php`.
4. Crear la conversión "whatsapp_lead" (evento ya dispara al pulsar cualquier botón wa.me).
5. Crear la campaña copiando `ads/campana_estructura.md` (presupuesto $5.000 CLP/día, Maipú).

## Seguridad antes de salir a producción
- (Hecho) Contraseñas admin123/editor123 reemplazadas por hashes seguros — cambia la sesión local si usabas las viejas.
- Cambiar `DB_PASS` si el hosting usa credenciales distintas.
- (Recomendado) Usar HTTPS obligatorio (certificado SSL del hosting).