# Deployment Guide

This guide explains how to deploy the Stock Trading Application using Docker.

## Prerequisites

- [Docker](https://www.docker.com/get-started) installed on your machine.
- [Docker Compose](https://docs.docker.com/compose/install/) (usually included with Docker Desktop).

## Quick Start (Docker)

1.  **Clone the repository** (if you haven't already).
2.  **Navigate to the project directory**:
    ```bash
    cd /path/to/project
    ```
3.  **Start the application**:
    ```bash
    docker-compose up -d --build
    ```
    This command will:
    - Build the PHP/Apache image.
    - Start the MySQL database container.
    - Automatically import the database schema and dummy data.
    - Start the web server.

4.  **Access the application**:
    Open your browser and go to: [http://localhost:8000](http://localhost:8000)

## Default Credentials

- **Database Root Password**: `Dhruba2232537`
- **Application User**: You can register a new account or use the dummy data if available.

## Troubleshooting

- **Database Connection Error**:
    - Ensure the `db` container is running: `docker-compose ps`
    - Check logs: `docker-compose logs db`
    - Wait a few seconds for MySQL to fully initialize.

- **Port Conflict**:
    - If port `8000` is in use, edit `docker-compose.yml` and change `"8000:80"` to another port like `"8080:80"`.

## Manual Deployment (Without Docker)

If you prefer to run it manually (e.g., with XAMPP/MAMP or local PHP):

1.  **Database**:
    - Create a database named `stock_trading_db`.
    - Import `database_enhanced.sql`.
    - Import `add_dummy_data.sql`.
    - Import `add_more_triggers.sql`.

2.  **Configuration**:
    - Copy `.env.example` to `.env`.
    - Update `.env` with your local database credentials.

3.  **Dependencies**:
    - Run `composer install` to install PHP libraries.

4.  **Run**:
    - Start your web server pointing to the project directory.
    - Or use PHP built-in server: `php -S localhost:8000`

## Online Deployment (Cloud VPS)

The recommended way to deploy online is using a Virtual Private Server (VPS) like **DigitalOcean**, **Linode**, or **AWS EC2**.

### Steps:

1.  **Provision a Server**:
    - Create a new Ubuntu 22.04 (or newer) Droplet/Instance.

2.  **Install Docker on the Server**:
    - SSH into your server: `ssh root@your-server-ip`
    - Run the installation script:
      ```bash
      curl -fsSL https://get.docker.com -o get-docker.sh
      sh get-docker.sh
      ```

3.  **Deploy the App**:
    - Clone your repository (or copy files via SCP):
      ```bash
      git clone https://github.com/your-username/your-repo.git app
      cd app
      ```
    - Create the production environment file:
      ```bash
      cp .env.example .env
      nano .env
      # Update DB_PASS and other credentials to strong values!
      ```
    - Start the application:
      ```bash
      docker compose up -d --build
      ```

4.  **Access Online**:
    - Visit `http://your-server-ip:8000`
    - (Optional) Set up Nginx as a reverse proxy to serve on port 80/443 with a domain name.

## Temporary Public Access (from Localhost)

If you want to show your local app to someone remotely *without* buying a server, you can use **ngrok**.

1.  **Install ngrok**: [Download and sign up](https://ngrok.com/download).
2.  **Start your app** locally (e.g., on port 8000).
3.  **Run ngrok**:
    ```bash
    ngrok http 8000
    ```
4.  **Share the URL**: ngrok will give you a public URL (e.g., `https://random-name.ngrok-free.app`) that anyone can visit to see your local app.

