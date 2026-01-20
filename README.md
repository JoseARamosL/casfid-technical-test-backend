# Casfid Technical Test - Backend Developer

Prueba técnica desarrollada con **Symfony 7**, **MongoDB ODM** y **Docker**.
Implementa una arquitectura limpia para scraping de noticias y una API RESTful CRUD.

## 🚀 Características Clave

* **Scraping Polimórfico:** Arquitectura extensible (Strategy Pattern) con soporte para *El País* y *El Mundo*.
* **Persistencia NoSQL:** MongoDB para almacenamiento de documentos evitando duplicados por URL.
* **API REST:** Endpoints CRUD completos con:
    * **DTOs:** Validación estricta de datos de entrada (`validator`).
    * **Serialization:** Control de salida mediante grupos (`news:read`).
* **Calidad:** Código siguiendo principios SOLID y tests unitarios/funcionales con PHPUnit.

## ⚡ Inicio Rápido (Makefile)

He incluido un `Makefile` para simplificar la gestión del proyecto:

1.  **Iniciar el entorno:**
    ```bash
    make start
    ```
2.  **Descargar noticias (Poblar BBDD):**
    ```bash
    make scrape
    ```
3.  **Ejecutar Tests:**
    ```bash
    make test
    ```

## 🔗 Documentación API

La API está disponible en `http://localhost:8890/feeds`.

| Método | Endpoint      | Descripción               | Cuerpo (JSON) |
| :---   | :---          | :---                      | :--- |
| GET    | `/feeds`      | Listar todas las noticias | N/A |
| POST   | `/feeds`      | Crear noticia             | `{"title": "...", "url": "...", "source": "Manual"}` |
| GET    | `/feeds/{id}` | Ver detalle               | N/A |
| PUT    | `/feeds/{id}` | Editar noticia            | `{"title": "Nuevo titulo"}` |
| DELETE | `/feeds/{id}` | Eliminar noticia          | N/A |

## 🛠️ Stack Tecnológico

* **PHP 8.2** + Symfony 7
* **MongoDB** + Doctrine ODM
* **Docker** + Nginx
