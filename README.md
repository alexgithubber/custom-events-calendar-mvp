# Events Calendar API with Weather

This is a small project that allows you to register events with their respective locations and dates, you get information about the weather forecast for such events when you retrieve them.

Obs: There may not be a forecast for dates 15 days ahead due to limitations in the Weather API free tier model.

## Setup

The project was developed with Docker, to setup the application, execute the following:

In the application directory, generate the .env file from the .env.example

    cp .env.example .env

Build the project

    docker-compose up -d

Install the dependencies

    docker exec -it api.customized-calendar.dev sh -c "composer install"

Run migrations

    docker exec -it api.customized-calendar.dev sh -c "php artisan migrate"

Seed the database

    docker exec -it api.customized-calendar.dev sh -c "php artisan db:seed"

At this point, the project should be up and running at http://localhost:8080/

> Now you can check the `users` table and pick up one email to login in the `http://localhost:8080/api/login` endpoint, the password is `password`

For the sending of emails, start the email worker
    
    php artisan queue:work  --queue=emails

**Observation**

I used the **Mailgun driver** for sending e-mails, if using the same driver, you just need to set the variables:

    MAILGUN_DOMAIN=
    MAILGUN_SECRET=

If you're using a different one, besides the other MAIL fields, you need to change the env MAIL_MAILER which is set in the *.env.example* to mailgun

Executing tests

    docker exec -it api.customized-calendar.dev sh -c "php ./vendor/bin/phpunit"



## Endpoints

### /api/users
- *POST* (create a new user)

payload
```json
  {
  "name":"User Name",
  "email":"user@test.com",
  "password":"123password"
  }
```
reponse
```json
{
    "status": "success",
    "access_token": "10|kFxztVAILjTOA6HJKzy4w45jsghfFASiNFEkcm6rMKo",
    "token_type": "Bearer"
}
```

### /api/login
- *POST* (retrieve the User authorization token)

payload
```json
 {
  "email":"stroman.narciso@example.net",
  "password":"123password"
}
```
reponse
```json
{
  "status": "success",
  "authorization": {
    "token": "11|0T4xImePYBhpVMK6LFo0ukPJJ5s4pIiYe7Q3smfY",
    "type": "bearer"
  }
}
```

> **Protected routes**: from now on, all endpoints require the Authorization Bearer token

### /api/event

- *POST* (add a new event)
    - location: the city and country code separated by comma (*New York,US*)
    - date: the event date in the format: YYYY-MM-DD hh:mm
    - invitees: a list containing the invited e-mails

#### payload
```json
{
  "location":"New York,US",
  "date":"2023-06-04 19:00",
  "invitees":[
    "stroman@example.com"
  ]
}
```
#### reponse
```json
{
  "status": "created",
  "event": {
    "id": 96,
    "location": "New York,US",
    "date": "2023-06-04 19:00",
    "invitees": [
      "stroman@example.com"
    ],
    "created_at": "2023-05-23 15:01:08"
  }
}
```

- *GET* (returns all events for the logged user including the weather forecast and pagination)
    - This endpoint accepts the query params [*from, to*] in the format YYYY-MM-DD, if both are informed, only the events between these two dates will be returned 
      - **Obs:** *if only one of the dates is informed no filtering will be applied*

reponse
```json
{
  "data": [
    {
      "id": 96,
      "location": "Santa Monica,US",
      "date": "2023-06-03 18:00",
      "invitees": [
        {
          "id": 143,
          "event_id": 96,
          "email": "stroman@example.com"
        }
      ],
      "created_at": "2023-05-23 15:01:08",
      "weather_forecast": {
        "description": "partly cloudy",
        "temperature": {
          "min": 16.6,
          "max": 20.8
        },
        "precipitation_chance": 0
      }
    },
    {
      "id": 95,
      "location": "Toronto,CA",
      "date": "2023-06-05 09:00",
      "invitees": [
        {
          "id": 142,
          "event_id": 95,
          "email": "stroman@example.com"
        }
      ],
      "created_at": "2023-05-23 14:55:46",
      "weather_forecast": {
        "description": "Drizzle: dense",
        "temperature": {
          "min": 10.1,
          "max": 16.8
        },
        "precipitation_chance": 0
      }
    }
  ],
  "links": {
    "first": "http://localhost:8080/api/events?page=1",
    "last": "http://localhost:8080/api/events?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://localhost:8080/api/events?page=1",
        "label": "1",
        "active": true
      },
      {
        "url": null,
        "label": "Next &raquo;",
        "active": false
      }
    ],
    "path": "http://localhost:8080/api/events",
    "per_page": 15,
    "to": 2,
    "total": 2
  }
}
```
- *GET with id* (returns the specified event for the logged user including the weather forecast)
- /api/event/{id}
```json
{
    "event": {
        "id": 96,
        "location": "Santa Monica,US",
        "date": "2023-06-03 18:00",
        "invitees": [
            {
                "id": 143,
                "event_id": 96,
                "email": "stroman@example.com"
            }
        ],
        "created_at": "2023-05-23 15:01:08",
        "weather_forecast": {
            "description": "partly cloudy",
            "temperature": {
                "min": 16.6,
                "max": 20.8
            },
            "precipitation_chance": 0
        }
    }
}
```

- *PUT/PATCH* (updates an existing event with the informed fields)
- /api/event/{id}
    - **Obs:** a new e-mail is sent to every invitee informing them about event update

#### payload (date, location or invitees)
```json
{
  "location":"Madrid,ES"
}
```
#### reponse
```json
{
  "status": "success",
  "event": {
    "id": 96,
    "location": "Madrid,ES",
    "date": "2023-06-03 18:00",
    "invitees": [
      "stroman@example.com"
    ],
    "created_at": "2023-05-23 15:01:08"
  }
}
```

- *DEL* (deletes an user existing event with the informed fields)
- /api/event/{id}
    - **Obs:** a new e-mail is sent to every invitee informing them about event update

#### reponse
```json
{
  "status": "success",
  "message": "Event with id 117 was deleted"
}
```

### /api/locations

- *GET* (retrieve the user event locations with weather forecast *[grouped by location, separated by date]*)
  - **from, to:** for this endpoint, the **from** and **to** params are mandatory

#### reponse
```json
{
  "Paris,FR": {
    "dates": [
      {
        "event_date": "2023-05-10",
        "weather_forecast": {
          "description": "Rain showers: slight",
          "temperature": {
            "min": 10.5,
            "max": 17.7
          },
          "precipitation_chance": 100
        }
      },
      {
        "event_date": "2023-05-25",
        "weather_forecast": {
          "description": "partly cloudy",
          "temperature": {
            "min": 10,
            "max": 21.2
          },
          "precipitation_chance": 6
        }
      }
    ]
  },
  "Tokyo,JP": {
    "dates": [
      {
        "event_date": "2023-05-23",
        "weather_forecast": {
          "description": "Rain: slight",
          "temperature": {
            "min": 13.1,
            "max": 15
          },
          "precipitation_chance": 100
        }
      }
    ]
  }
}
```

