# Campaña Google Ads — Veterinaria 5 de Abril

**Copia/pega esta estructura en tu cuenta de Google Ads** (la creas en ads.google.com con un correo + tarjeta de pago). Una vez creados los datos, no inventes nada: sigue este documento.

## Datos base
- Negocio: Veterinaria 5 de Abril
- Dirección: Av. Lafquén 260, Maipú, Santiago de Chile
- WhatsApp/Conversión: +569 9599 9482 (click a wa.me = lead)
- Presupuesto: **$5.000 CLP / día**
- Objetivo: agendamiento (leads por WhatsApp)
- Servicios a promocionar: Atención general · Vacunas · Castración

---

## 1. Configuración general de la campaña

| Ajuste | Valor |
|---|---|
| Tipo de campaña | **Búsqueda** |
| Nombre | `Vet5Abril - Maipú - Servicios` |
| Red | Google search network SOLO (desmarcar Search partners y Display, apps) |
| Idioma | Español |
| Ubicaciones | **Maipú** (radio 10 km), + Pudahuel, Cerrillos, Estación Central, Lo Prado |
| Presupuesto | $5.000 CLP/día |
| Pujas | **Maximizar conversiones** (si el tag está instalado). Si no hay conversiones aún, empezar con **CPC manual máx. $900 CLP** |
| Fecha inicio | Hoy |
| Programación | Todos los días, 08:00–22:00 hora local |
| Extensiones | Llamada (+569 9599 9482) · Ubicación (dirección) · Promoción (¿vacuna?) · Enlaces de sitio (Atención, Vacunas, Castración) |

---

## 2. Grupos de anuncios y palabras clave

Regla: usar **Exacta** `[ ]` primero (control de gasto). Añadir Frase `" "` solo si faltan impresiones.

### Grupo A — Atención general
| Tipo | Palabra |
|---|---|
| Exacta | [veterinaria maipú] |
| Exacta | [veterinaria en maipú] |
| Exacta | [veterinaria cerca de mi] |
| Exacta | [veterinaria lafquén maipú] |
| Exacta | [veterinaria de mascotas maipú] |
| Frase | "veterinaria maipú" |
| Frase | "veterinaria lafquén" |

### Grupo B — Vacunas
| Tipo | Palabra |
|---|---|
| Exacta | [vacuna antirrábica maipú] |
| Exacta | [vacuna perro maipú] |
| Exacta | [vacuna gato maipú] |
| Exacta | [vacuna polivalente maipú] |
| Exacta | [vacunación mascotas maipú] |
| Frase | "vacunas para perros maipú" |

### Grupo C — Castración
| Tipo | Palabra |
|---|---|
| Exacta | [castración gato maipú] |
| Exacta | [castración perro maipú] |
| Exacta | [castración de gatos precio maipú] |
| Exacta | [esterilización mascotas maipú] |
| Frase | "castración de gatos" |
| Frase | "castración de perros" |

---

## 3. Anuncios (texto) — un par por grupo

### Grupo A — Atención general
- **EncH1:** Veterinaria en Maipú
- **EncH2:** Av. Lafquén 260
- **EncH3:** Agenda por WhatsApp
- **Desc1:** Consultas y urgencias. Precios claros, atención con cariño. Agenda hoy mismo por WhatsApp.
- **Desc2:** Perros y gatos. Atención de lunes a sábado en Maipú.

### Grupo B — Vacunas
- **EncH1:** Vacunas para Mascotas
- **EncH2:** En Maipú, Certificadas SAG
- **EncH3:** Agenda por WhatsApp
- **Desc1:** Vacuna antirrábica y polivalente para perros y gatos. Precios claros en Maipú.
- **Desc2:** Agenda hoy y deja las vacunas al día. Av. Lafquén 260, Maipú.

### Grupo C — Castración
- **EncH1:** Castración Perros y Gatos
- **EncH2:** En Maipú
- **EncH3:** Consulta Precio por WhatsApp
- **Desc1:** Esterilización segura con seguimiento post-operatorio. Precios claros en Maipú.
- **Desc2:** Agenda hoy. Av. Lafquén 260, Maipú. +569 9599 9482.

Reglas de encabezados: no repetir palabras, 30 caracteres máx, 2 titulares al menos, 15 descripciones máx. Al crear, completa TODAS las variantes que pida Google.

---

## 4. Palabras clave negativas (a nivel de campaña)

```
gratis, barato, rasa, segunda mano, usada, adopción, refugio, fundación,
rescatistas, tienda, alimento, accesorios, zoológico, estudiante, práctica,
prácticas, profesor, curso, online, a distancia, asesoría, domicilio,
emergencia gratuita, vacuna gratis, castración gratis, esterilización gratis,
plan maestro, vitamina, colágeno, libros, películas, juegos, veterinario carta
```

*(La "e" en ras*se refiere a "casa rasa": excluye contexto inmobiliario. Revisa el reporte de términos 2 semanas y añade más.)*

---

## 5. Conversión (imprescindible)

1. En el sitio ya está el **Google Tag** (configura `GA_MEASUREMENT_ID` en `config.php` con tu Measurement ID `G-XXXX`).
2. En Google Ads → Objetivos → Conversiones → **Nueva conversión** → "Sitio web" → opción **"Google Tag"** → el evento `whatsapp_lead` se registra automáticamente (categoría "Lead", valor: dejar 0 o asignar un valor de lead estimado).
3. Marcar la acción como primaria. Esperar 24-48h antes de juzgar.
4. Opcional: conversión de **llamada** asociada a la extensión de llamada.

---

## 6. Seguimiento (primeras 2-3 semanas)

- **Semana 1:** dejar correr sin tocar. Revisar solo "términos de búsqueda" → añadir negativas de lo que no sirve.
- **Semana 2:** subir/bajar puja según CPC real; si Maximizar conversiones trabaja, no tocar CPC.
- **Semana 3:** doblar presupuesto si el CPA (costo por WhatsApp) es razonable (< $1.500 CLP ideal); pausar grupos sin conversiones si tras N clics no rinden.

## KPI objetivo
- CPA WhatsApp: < $2.000 CLP
- CTR: > 4% (búsqueda con buena landing)
- Volumen esperado con $5.000/día: 10-15 clics/día ≈ 15-25 leads/semana (según búsquedas de Maipú)

---

## Checklist (orden de ejecución)
1. [ ] Hosting + dominio `.cl` comprados (Hostinger, PHP+MySQL+SSL)
2. [ ] BD creada; `setup.sql` importado desde phpMyAdmin
3. [ ] Archivos subidos a `public_html/`; `config.php` editado (BD, SITE_URL, WHATSAPP_NUMBER)
4. [ ] SSL activo (HTTPS) y URL final funcionando
5. [ ] Cuenta de Google Ads creada + verificación de dominio
6. [ ] `GA_MEASUREMENT_ID` puesto en `config.php`; evento `whatsapp_lead` verificado (GA DebugView o tag assistant)
7. [ ] Campaña creada con este documento
8. [ ] 2-3 semanas de ajustes con datos reales