composer require --dev symfony/test-pack
exit
php bin/console make:migration
php bin/console doctrine:migrations:migrate
exit
