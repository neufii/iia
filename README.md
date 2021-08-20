# Framework for Inexhaustible Indicator-based Assessment with Automatic Question Generator
## Installation

### Requirements
- composer
- NodeJS
- python3

### Getting Start

#### 1. Clone The IIA Repository
Clone the project from [Github](github.com/neufii/iia)

#### 2. cd into the project
Open a terminal and use the command below.
```
cd /path/to/ProjectName
```

#### 3. Install Packages
Run commands below to install PHP packages and Node dependencies
```
composer install
npm install
```

#### 4. Setup Database
Create an empty database for a project.

#### 5. Copy .env file
The `.env` file is not uploaded to the github, so we need to copy from `.env.example` with the following command.
```
cp .env.example .env
```

#### 6. Setup .env file
Run `php artisan key:generate` to add a key for `APP_KEY` in .env file. Then, fill `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` to match the database created in step 4.

#### 7. Migrate
Run `php artisan migrate:fresh` to migrate all the tables.


### More Information
Please visit https://iia-framework.web.app