# WatchParty – aplikacja Laravel

Aplikacja REST API do organizowania wspólnych seansów filmowych, zbudowana w oparciu o framework Laravel (PHP).

---

## Wymagania wstępne

Przed przystąpieniem do instalacji upewnij się, że masz zainstalowane poniższe narzędzia.

### 1. PHP 8.2+

Sprawdź, czy PHP jest zainstalowane:

```bash
php -v
```

Jeśli nie – zainstaluj zgodnie z systemem operacyjnym:

- **Ubuntu/Debian:**
    ```bash
    sudo apt update
    sudo apt install php8.2 php8.2-cli php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-mysql php8.2-pdo
    ```
- **macOS (Homebrew):**
    ```bash
    brew install php
    ```
- **Windows:** pobierz instalator ze strony [https://windows.php.net/download](https://windows.php.net/download) i dodaj PHP do zmiennej środowiskowej `PATH`.

### 2. Composer

Composer to menedżer pakietów dla PHP.

Sprawdź, czy jest zainstalowany:

```bash
composer -V
```

Jeśli nie – zainstaluj zgodnie z oficjalną dokumentacją: [https://getcomposer.org/download](https://getcomposer.org/download)

Przykład dla Linux/macOS:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
```

### 3. Node.js 18+ i npm

Wymagane do kompilacji front-endowych zasobów przez Vite.

Sprawdź, czy jest zainstalowany:

```bash
node -v
npm -v
```

Jeśli nie – pobierz instalator ze strony [https://nodejs.org](https://nodejs.org) (zalecana wersja LTS).

Linux (via nvm):

```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
nvm install --lts
```

### 4. MySQL 8+

Aplikacja korzysta z bazy danych MySQL.

- **Ubuntu/Debian:**
    ```bash
    sudo apt update
    sudo apt install mysql-server
    sudo systemctl start mysql
    sudo systemctl enable mysql
    ```
- **macOS (Homebrew):**
    ```bash
    brew install mysql
    brew services start mysql
    ```
- **Windows:** pobierz MySQL Installer ze strony [https://dev.mysql.com/downloads/installer](https://dev.mysql.com/downloads/installer).

### 5. Klucze API

Aplikacja korzysta z zewnętrznego API do pobierania informacji o filmach:

- **OMDb API** – utwórz bezpłatne konto na [https://www.omdbapi.com/apikey.aspx](https://www.omdbapi.com/apikey.aspx), aby uzyskać klucz API.

---

## Pobranie projektu

Sklonuj repozytorium za pomocą Git:

```bash
git clone https://github.com/oskarziembrowicz/masters-degree-project.git
cd masters-degree-project/WatchParty-Laravel-App
```

Jeśli Git nie jest zainstalowany:

- **Ubuntu/Debian:** `sudo apt install git`
- **macOS:** `brew install git`
- **Windows:** pobierz z [https://git-scm.com/download/win](https://git-scm.com/download/win)

---

## Konfiguracja bazy danych

Zaloguj się do MySQL jako root i utwórz bazę danych oraz użytkownika:

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE watch_party_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'watchparty'@'localhost' IDENTIFIED BY 'watchparty';
GRANT ALL PRIVILEGES ON watch_party_db.* TO 'watchparty'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## Konfiguracja pliku `.env`

Skopiuj przykładowy plik konfiguracyjny:

```bash
cp .env.example .env
```

Otwórz plik `.env` i dostosuj wartości do swojego środowiska. Najważniejsze sekcje:

```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=watch_party_db
DB_USERNAME=watchparty
DB_PASSWORD=watchparty

# Klucz API
OMDB_API_KEY=twój_klucz_omdb_api
```

Jeśli podczas tworzenia użytkownika MySQL ustawiłeś inne hasło lub nazwę użytkownika, zmień odpowiednio `DB_USERNAME` i `DB_PASSWORD`.

---

## Instalacja zależności

### Zależności PHP (Composer)

```bash
composer install
```

### Zależności JavaScript (npm)

```bash
npm install
```

---

## Generowanie klucza aplikacji

Laravel wymaga unikalnego klucza szyfrowania. Wygeneruj go poleceniem:

```bash
php artisan key:generate
```

Klucz zostanie automatycznie zapisany do pliku `.env`.

---

## Uruchomienie migracji bazy danych

Utwórz strukturę tabel w bazie danych:

```bash
php artisan migrate
```

Opcjonalnie – jeśli chcesz wypełnić bazę przykładowymi danymi (seeders):

```bash
php artisan db:seed
```

---

## Uruchomienie aplikacji

```bash
php artisan serve
```

---

## Podsumowanie – kolejność kroków

1. Zainstaluj PHP 8.2+, Composer, Node.js, MySQL
2. Sklonuj repozytorium
3. Przejdź do katalogu `WatchParty-Laravel-App`
4. Utwórz bazę danych i użytkownika MySQL
5. Skopiuj `.env.example` do `.env` i skonfiguruj dane bazy
6. Uruchom `composer install`
7. Uruchom `npm install`
8. Uruchom `php artisan key:generate`
9. Uruchom `php artisan migrate`
10. Uruchom `php artisan serve`
