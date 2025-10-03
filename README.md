# Laravel Application

This repository contains the source code for a Laravel application built with the Laravel framework.

## 🚀 Getting Started

Follow these steps to set up and run the application locally.

---

### ✅ Prerequisites

Ensure you have the following installed:

-   **PHP** (version 8.0 or later recommended)
-   **Composer**
-   **MySQL** or another supported database
-   **Node.js** and **npm** (for frontend assets)

---

### 🔧 Installation

1. **Clone the repository:**

    ```bash
    git clone https://github.com/dale0333/jobsportalBackend.git
    ```

2. **Navigate to the project directory:**

    ```bash
    cd jobsportal-laravel
    ```

3. **Install PHP dependencies using Composer:**

    ```bash
    composer install
    ```

4. **Install frontend dependencies using npm:**

    ```bash
    npm install
    ```

5. **Copy the example environment file and configure it:**

    ```bash
    cp .env.example .env
    ```

    Update the `.env` file with your database and application settings.

6. **Run the database migrations:**
    ```bash
    php artisan migrate
    ```

---

### ▶️ Running the Application

Start the Laravel development server:

```bash
php artisan serve
```

Open your browser and visit:  
[http://127.0.0.1:8000](http://127.0.0.1:8000)
