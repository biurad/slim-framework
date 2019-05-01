<?php

/**
 * Class DatabaseFactory.
 *
 * Use it like this:
 * $database = DatabaseFactory::getFactory()->getConnection();
 *
 * That's my personal favourite when creating a database connection.
 * It's a slightly modified version of Jon Raphaelson's excellent answer on StackOverflow:
 * http://stackoverflow.com/questions/130878/global-or-singleton-for-database-connection
 *
 * Full quote from the answer:
 *
 * "Then, in 6 months when your app is super famous and getting dugg and slashdotted and you decide you need more than
 * a single connection, all you have to do is implement some pooling in the getConnection() method. Or if you decide
 * that you want a wrapper that implements SQL logging, you can pass a PDO subclass. Or if you decide you want a new
 * connection on every invocation, you can do do that. It's flexible, instead of rigid."
 *
 * Thanks! Big up, mate!
 */
class DatabaseFactory
{
    private static $factory;
    private $database;

    public static function getFactory()
    {
        if (!self::$factory) {
            self::$factory = new self();
        }

        return self::$factory;
    }

    public function getConnection()
    {
        if (!$this->database) {

            /*
             * Check DB connection in try/catch block. Also when PDO is not constructed properly,
             * prevent to exposing database host, username and password in plain text as:
             * PDO->__construct('mysql:host=127....', 'root', '12345678', Array)
             * by throwing custom error message
             */
            try {
                $options = [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING];
                $this->database = new PDO(
                    Config::get('database', 'driver').':host='.Config::get('database', 'host').';dbname='.
                        Config::get('database', 'database').';port='.Config::get('database', 'port').';charset='.Config::get('database', 'charset'),
                    Config::get('database', 'username'),
                    Config::get('database', 'password'),
                    $options
                );
            } catch (PDOException $e) {

                // Echo custom message. Echo error code gives you some info.
                Debugger::display('Hmmmmm!', 'Database connection can not be estabilished Error code: '.$e->getCode());

                // Stop application :(
                // No connection, reached limit connections etc. so no point to keep it running
                exit;
            }
        }

        return $this->database;
    }
}
