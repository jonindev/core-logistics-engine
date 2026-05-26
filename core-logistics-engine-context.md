# Proyecto: Core Logistics Engine (MVP)

## 1. Contexto de Negocio
El sistema está diseñado para resolver los cuellos de botella en la gestión de logística transfronteriza. Actualmente, los operadores enfrentan dos problemas principales: la sobrecarga del servidor al recibir miles de actualizaciones de paquetes simultáneas (webhooks) y la rigidez de la base de datos para integrar nuevos couriers.

## 2. Objetivos del Sistema
* **Desacoplar la ingesta de datos:** Permitir que el sistema acepte información masiva sin bloquear la experiencia del usuario.
* **Flexibilidad operativa:** Almacenar datos dinámicos de aduanas y dimensiones mediante estructuras flexibles.
* **Eficiencia en la consulta:** Garantizar que los operadores puedan visualizar y buscar envíos en tiempo real sin lentitud en el dashboard.

## 3. Especificaciones Técnicas

### A. Capa de Datos (PostgreSQL + JSONB)
* **Tabla `shipments`:**
    * `tracking_number` (String, Indexado para búsqueda rápida).
    * `metadata` (Tipo **JSONB** para almacenar atributos variables del envío).
* **Índices:** Implementación de índice **GIN** sobre la columna `metadata` para habilitar búsquedas ultra-rápidas dentro del contenido JSON.
* **Integridad:** Uso de llaves foráneas con `onDelete('cascade')` para asegurar la consistencia.

### B. Ingesta y Procesamiento (Event-Driven)
* **Colas (Redis):** Cada actualización recibida desde un courier se delega a un `Job` en segundo plano. Esto permite responder inmediatamente al proveedor con un `200 OK` mientras el sistema procesa la lógica internamente.
* **Resiliencia:** Configuración de `tries = 3` y `backoff` en los trabajos (Jobs) para gestionar fallos temporales de red sin intervención manual.

### C. Dashboard de Visualización (La Vista)
* **Dashboard Centralizado:** Vista única que agrupa envíos de todos los *carriers* mediante una consulta paginada (`paginate(20)`).
* **Optimización (Eager Loading):** Implementación de `Shipment::with('carrier')->latest()->get()` para resolver el problema N+1, logrando que el dashboard cargue con solo dos consultas a la base de datos.
* **Filtros dinámicos:** Buscador integrado que consulta directamente sobre los campos dentro del `JSONB` para filtrar por estado o tipo de servicio sin afectar el rendimiento.

## 4. Estructura del Entregable

| Componente | Objetivo |
| :--- | :--- |
| `docker-compose.yml` | Estandarizar el entorno de desarrollo (PHP, Postgres, Redis). |
| `ProcessWebhookJob` | Gestión de colas y reintentos automáticos. |
| `ShipmentController` | Lógica de visualización optimizada con Eager Loading. |
| `ExplainAnalysis.md` | Documentación técnica de cómo el índice GIN optimiza las búsquedas. |

## 5. Argumentos para la Entrevista (El "Senior Pitch")

> "Construí este proyecto para demostrar cómo resolvería la escalabilidad de la empresa. 
> 
> Primero, usé **colas** porque en logística la ingesta es masiva; no podemos dejar que el usuario espere a que la base de datos procese un webhook. Segundo, elegí **PostgreSQL con JSONB** porque entiendo que los requisitos de aduana cambian constantemente; esto nos da flexibilidad sin sacrificar rendimiento gracias a los **índices GIN**. 
> 
> Finalmente, cuido la experiencia del operador: en el dashboard, apliqué **Eager Loading** para que la vista sea instantánea, evitando consultas innecesarias a la base de datos. Es una arquitectura diseñada para crecer, no para romperse."