Загальна інформація:

Ендпоінт для отримання токену: localhost/v1/api/login_check
Приймає в себе 2 параметри для логіну: login, pass
В  мому випадку я використовував не два різні токени, а по принципу SecurityBundle в Symfony,за допомогою ролей.

Дані для логіну як адмін:
`{
    "login" : "admin",
    "pass" : "admin"
}`

Дані для логіну без адмінських прав:
`{
    "login": "user",
    "pass": "user"
}`

В проєкті готова конфігурація для підняття проєкту за допомогою Docker.

Контроль за ролями та доволами для користувача обробляються в Voter-і (/src/Security/Voter)

З приводу помилок в тестовому завданні:

1. Валідація по 8 символів на все (як на мене це не вірно, так як більшість з цих даних може мати більше символів).
2. Унікальні ключі для пароля та логіну (неправильно ставити унікальний ключ для пароля)

