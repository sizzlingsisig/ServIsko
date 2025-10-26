#Instructions to make this work

## 📋 Prerequisites

- PHP 8.1+
- Composer
- Node.js 18+
- PostgreSQL 8.0+
- Git

## Installation
### Complete Setup (Copy and Paste)
1. Clone the repository
   ```git clone https://github.com/sizzlingsisig/ServIsko.git```
2. Go into Servisko directory
3. Go to backend directory
```
cd backend
composer install
cd backend
composer install
cp .env.example .env
php artisan key:generate
```
4. Configure database (edit .env file manually)
Set: DB_DATABASE=servisko, DB_USERNAME=root, DB_PASSWORD=your_password
```
php artisan migrate
```
5. Go to frontend
6. Install dependencies
```  npm install```
7. Start frontend server
```
npm run dev
```
