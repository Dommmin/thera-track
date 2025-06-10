TheraTrack 2.0 — Symfony 7.3 + Twig
 Cel aplikacji
Aplikacja do zarządzania rezerwacjami sesji terapeutycznych między pacjentami (users) a terapeutami.
Każdy użytkownik może mieć rolę pacjenta lub terapeuty. Terapeuci ustalają dostępność oraz stawkę
godzinową, a pacjenci rezerwują sesje.
 Role
ROLE_USER – pacjent (widzi wolne terminy, rezerwuje, zarządza swoimi wizytami)
ROLE_THERAPIST – terapeuta (tworzy dostępność, ustawia stawkę, zarządza swoimi wizytami)
ROLE_ADMIN – administrator (zarządza użytkownikami i wizytami)
 Struktura katalogów
src/
├── Controller/
│ ├── AppointmentController.php
│ ├── TherapistController.php
│ └── AdminController.php
├── Entity/
│ ├── User.php
│ ├── Appointment.php
│ └── Availability.php
├── Form/
│ ├── AppointmentBookingType.php
│ ├── AvailabilityFormType.php
│ └── TherapistSettingsFormType.php
├── Message/
│ └── AppointmentReminderMessage.php
├── MessageHandler/
│ └── AppointmentReminderHandler.php
├── Security/
│ └── AppointmentVoter.php
└── Service/
 └── CalendarService.php (opcjonalnie dla integracji Google)
