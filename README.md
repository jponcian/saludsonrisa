# Proyecto Clínica / Demo Reuniones + Streaming

## Contenido

- Gestión clínica (pacientes, especialistas, usuarios, facturación)
- Módulo demo de reuniones con:
  - Registro de participantes y asistencia vía QR
  - Transmisión en vivo WebRTC (PeerJS)
  - Fallback por imágenes JPEG (HTTP polling) cuando WebRTC falla (sin TURN o bloqueo NAT)
  - Integración dinámica con TURN (Xirsys) con caché local

## Requisitos Servidor

- PHP >= 7.4 (cURL habilitado para TURN dinámico)
- Extensión PDO + driver MySQL
- Servidor web (Apache / Nginx) con HTTPS recomendado para getUserMedia/WebRTC
- Certificado SSL válido (en producción). En HTTP algunos navegadores bloquearán cámara/mic.

## Estructura Clave

`demo/transmitir.php` Página del transmisor
`demo/qr_info.php` Página del participante (viewer) tras escanear QR
`demo/webrtc_config.php` Carga/mezcla servidores ICE (STUN/TURN)
`demo/webrtc_secrets.php` Credenciales Xirsys (IGNORADO por git)
`demo/upload_frame.php` Endpoint para frames fallback
`demo/frames/current.jpg` Último frame generado (creado en runtime)

## Configuración TURN (Xirsys)

1. Crea/edita `demo/webrtc_secrets.php` (no subir a git):

```php
<?php
return [
	'xirsys' => [
		'user' => 'TU_USER',
		'secret' => 'TU_SECRET',
		'channel' => 'personal',
		'cache_ttl' => 300
	]
];
```

2. Verifica petición directa: abre en navegador `https://TU_DOMINIO/demo/webrtc_config.php` y confirma que devuelve JSON con nodos `turn:`.
3. Si falla la petición, el sistema usará sólo STUN (funciona en misma red, pero no a través de NAT restrictivo).

## Flujo de Transmisión

1. Transmisor abre `transmitir.php?id=ID_REUNION` e inicia cámara.
2. Se crea Peer con ID `transmisor-ID_REUNION`.
3. Viewer (participante) accede a `qr_info.php?reunion=ID&participante=PID&token=TOKEN`.
4. Viewer abre DataChannel y envía `solicitar-stream`.
5. Transmisor inicia llamada WebRTC al viewer y su stream fluye.
6. Si WebRTC no se establece:
   - Viewer intenta llamada directa con stream silencioso.
   - Tras timeout entra fallback de imágenes (`frames/current.jpg`), refrescando cada 1.5 s.

## Ajustes de Calidad

En `transmitir.php` (función iniciarEnvioFrames):

- Cambia `toDataURL('image/jpeg', 0.6)` para subir/bajar calidad (0.4–0.7 recomendado).
- Cambia intervalo (1000 ms) para más FPS (más carga) o menos (ahorra ancho de banda).

## Seguridad / Producción

- Mueve credenciales a variables de entorno y en `webrtc_secrets.php` usa `getenv()`.
- Asegura permisos de escritura sólo donde se necesita (`demo/frames/`, `uploads/`).
- Sirve siempre por HTTPS para evitar bloqueos de cámara y mejorar NAT traversal.
- Limita acceso a `upload_frame.php` (opcional: token de sesión del transmisor).

## Despliegue Rápido

1. Subir todo excepto lo ignorado (.gitignore).
2. Crear base de datos y ejecutar dump `javier_ponciano_5.sql` (ajusta nombre si es necesario).
3. Configurar credenciales DB en `api/conexion.php`.
4. Crear `demo/webrtc_secrets.php` con tus credenciales.
5. Verificar permisos de escritura en `demo/frames/` (el script la crea si no existe).
6. Abrir página transmisor y luego escanear QR como viewer.

## Troubleshooting

| Problema                         | Causa Probable                        | Solución                                       |
| -------------------------------- | ------------------------------------- | ---------------------------------------------- |
| No aparece video fuera de la LAN | Falta TURN                            | Verificar `webrtc_config.php` devuelve TURN    |
| Sólo se ven imágenes cada 1.5s   | Falló WebRTC y entró fallback         | Revisar puertos/cortafuegos, activar TURN      |
| Cámara no inicia                 | Navegador bloquea permisos o no HTTPS | Usar dominio HTTPS válido                      |
| Frames muy lentos                | Intervalo alto o red saturada         | Bajar compresión o aumentar FPS con precaución |
| Demasiado ancho de banda         | Calidad JPEG alta o FPS alto          | Bajar quality a 0.5 y/o subir intervalo        |

## Extender

- Agregar reconexión automática si se pierde ICE.
- Endpoint JSON para estado del transmisor (último timestamp < N segundos).
- Botón manual en viewer para forzar modo imágenes.
- Métricas: contar viewers activos (array de conexiones DataChannel).

## Licencia / Nota

Revisa dependencias externas (PeerJS, AdminLTE, FontAwesome) y sus licencias antes de distribución pública.
