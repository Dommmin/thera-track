# TheraTrack - Therapy Appointment Management System

TheraTrack is a web application for managing therapy appointments between therapists and clients. It provides features for scheduling, managing availability, and handling appointments with email notifications and Google Calendar integration.

## Features

- User roles: Client (ROLE_USER) and Therapist (ROLE_THERAPIST)
- Appointment scheduling and management
- Availability management for therapists
- Email notifications for appointments
- Google Calendar integration
- Modern UI with Tailwind CSS and DaisyUI
- Full calendar view for therapists

## Requirements

- PHP 8.2 or higher
- Docker and Docker Compose
- Google Calendar API credentials (for calendar integration)
- SMTP server (for email notifications)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/theratrack.git
cd theratrack
```

2. Copy the environment file and configure it:
```bash
cp .env.example .env
```

3. Update the following environment variables in `.env`:
```
# Database
DB_USER=app
DB_PASSWORD=!ChangeMe!
DB_DATABASE=app

# Mailer
MAILER_DSN=smtp://localhost:1025

# Google Calendar API
GOOGLE_CALENDAR_CLIENT_ID=your_client_id
GOOGLE_CALENDAR_CLIENT_SECRET=your_client_secret
GOOGLE_CALENDAR_REDIRECT_URI=http://localhost/oauth2callback
```

4. Install and start the application:
```bash
make install
```

This will:
- Start the Docker containers
- Install dependencies
- Create the database
- Run migrations
- Load fixtures

## Usage

### Starting the Application

```bash
make start
```

### Stopping the Application

```bash
make stop
```

### Running Tests

```bash
make test
```

### Code Quality

```bash
make lint    # Run code style checks
make cs-fix  # Fix code style issues
```

## User Roles

### Client (ROLE_USER)
- View available appointment slots
- Book appointments
- View and manage their appointments
- Receive email notifications

### Therapist (ROLE_THERAPIST)
- Set working hours and availability
- Set hourly rate
- View and manage appointments
- View calendar with appointments
- Receive email notifications
- Mark specific days as unavailable (holidays, time off)
- Default availability: Monday-Friday during working hours, weekends unavailable

## Google Calendar Integration

To enable Google Calendar integration:

1. Create a project in the Google Cloud Console
2. Enable the Google Calendar API
3. Create credentials (OAuth 2.0 Client ID)
4. Add the credentials to your `.env` file:
   - GOOGLE_CALENDAR_CLIENT_ID
   - GOOGLE_CALENDAR_CLIENT_SECRET
   - GOOGLE_CALENDAR_REDIRECT_URI

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details. 