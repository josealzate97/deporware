# deporware

Plataforma de gestion deportiva basada en Laravel 12. Este proyecto parte de un sistema de inventario/ventas y se adapta para administrar clubes, categorias, clientes y operaciones relacionadas.

## Caracteristicas

- Gestion de usuarios y roles
- Modulos de productos, categorias, clientes y ventas (base para gestion deportiva)
- Reportes con exportacion a Excel y PDF
- Ajustes y configuracion del sistema
- Panel administrativo responsivo
- Docker + Nginx + MySQL

## Tecnologias

- PHP 8.2+
- Laravel 12
- Vite
- TailwindCSS
- Vue.js
- Docker
- Nginx
- MySQL 8

## Requisitos

- PHP 8.2+
- Composer
- Node 18+
- MySQL 8

## Instalacion local (sin Docker)

```bash
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run dev
```

Ajusta los valores de `.env` segun tu entorno.

## Instalacion con Docker

1. Configura `.env` con:
`PROJECT_NAME`, `DOMAIN_NAME`, `PHPMYADMIN_DOMAIN_NAME` y credenciales de base de datos.

2. Levanta los servicios:

```bash
docker compose up -d --build
```

Si usas Traefik, asegurate de tener la red externa `app` creada.

## Acceso

- App: http://localhost (o el dominio configurado)
- Vite: http://localhost:5173

## Licencia

MIT
