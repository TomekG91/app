### Wyszukiwarka repozytoriow organizacji na githubie

### Wymagania
* php >= 8.1
* composer

### Instalacja
* composer install

### Uruchomienie
* cd public
* php -S 127.0.0.1:8000

### Konfiguracja
* .env
   * APP_GITHUB_API_TOKEN - token umozliwiajacy autentykacje do [GitHub REST API](https://docs.github.com/en/rest). Instrukcja do otrzymania tokena znajduje sie [tutaj](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/creating-a-personal-access-token). Przy wyborze scopes wystarczy zaznaczyc repo.
* public\assets\js\config.js
   * APP_API_URL = "127.0.0.1";
   * APP_API_PORT = "8000";
   * APP_API_PROTOCOL = "http";
