# User-Team Management API Test Assignment
Hello there! This is my submission for the test assigment for Aderize (the best company in the world? Maybe...). This project uses the following stack:

* PHP 8.1+
* Symfony 6.4
* Doctrine
* NelmioApiDocBundle (for API documentation)
* Twig and Assets (needed for SwaggerUI)
* SQLite

It was developed on Windows 11 using Composer. For more details of the packages used you can visit the **composer.json** file (although I'm sure you know that).

## Setup Instructions

The setup assumes that all the needed requirements are installed already.

### 1. Clone the Repository

```bash
git clone https://github.com/MartinKpl/UserTeamManagementAPIAderize.git
cd UserTeamManagementAPIAderize
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Create the SQLite Database

Since the **.env** file already is set up to use SQLite you should only need to run:

```bash
php bin/console doctrine:database:create
```

### 4. Run Migrations

```bash
php bin/console doctrine:migrations:migrate
```

### 5. Run the code!

Since I had the Symfony CLI installed I just ran:

```bash
symfony serve
```

But should also work with:

```bash
php -S localhost:8000 -t public
```

Either way the server should be up and running at [http://localhost:8000](http://localhost:8000).

## 6. API Documentation with SwaggerUI

Visit [http://localhost:8000/api/doc](http://localhost:8000/api/doc) in the browser and you should be able to see the SwaggerUI.

## What would I have done differently?

* At the **GET /api/users** endpoint I added the bonus feature to search users by name or email, and in retrospective it would have maybe been better to make a separate endpoint for this functionality, specially after also adding the paging bonus feature.
* When creating the User-Team Relationship entity with the **make:migration** utility I chose the ManyToMany field type trusting that this would be the best practice. Now, as I kept developing the project I realised this was not how I would have prefered to have done the relationship, and I would have prefered to make 2 OneToMany fields that each would be the foreign key to each related Users and Teams entity. (If you actually read this, I would like to know what would actually have been the proper way).
* I created the entities with their names in plural since I was specially thinking about them as DB tables and that naming is what I'm used to. In the code it leads to a bit of naming confusing at some points, although that would have happened the other way around I think.
* I would have done testing, but to be 100% honest, I prefer to do other things with my life if I'm not getting payed for it, of course.

## What have you used the AI for, you lazy bastard?

Since I've been told that many others have used AI in a abusive way to make the project I decided to be 100% transparent with it so you can assess with more precision and understanding.

For starters I used it to set up properly the project, this is because in the previous profesional projects I worked on they were already set up and also the stack was not exactly the same, so though that getting some help for that was good to have a proper start.

Writing the documentation, as far as I know, has a consensus among programmers that is boring as heck. So I made AI write many of it. I obviously supervised that it's actually what I want, but since this was one of the last things I did I was pretty tired, so some strange things may have squeezed through my 2 remaining neurons.

And of course I used it through some incertanties on how to implement certain things, but never copy pasting straight away, I first understood what the AI was making and afterwards writting it myself making the needed modifications to fit the needs of whatever I was implementing at the moment.

As I said to Adrian on our first meeting, I use AI as substitute or complimentary to Google, not as an agent to do the work for me (except a bit in the documentation part as I mentioned previously) but as a tool to make things faster or solve doubts in a more personalized or targeted way. I must note that I developed in PHPStorm and this IDE has a pretty heavy autocompletion tool, I'm honestly not sure if this counts as AI too, but thought of letting you know it aswell.

As a final remark, I'm up to explaining any part of the code I wrote, despite my very bad memory I think I will be able to explain why I made certain decisions that you may think are suspicious.

## Possible improvements

* Make a FrontEnd page
* Testing
* Adding caches