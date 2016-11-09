<?php
namespace App\Model;

use Nette\Database\UniqueConstraintViolationException;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\Passwords;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * Class UserManager
 * @package App\Model
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Model na správu uživateľov
 */
class UserManager extends BaseManager implements IAuthenticator
{
    const
        USERS_TABLE = 'users',
        AWARDS_TABLE = 'users_achviements',
        AWARDS_TABLE_PROFILE = 'users_achviements_profile',
        COLUMN_ID = 'id',
        COLUMN_NAME = 'username',
        COLUMN_PASSWORD_HASH = 'password',
        COLUMN_ROLE = 'role',
        COLUMN_PREMIUM = 'premium',
        COLUMN_EMAIL = 'email',
        TABLE_PERMISSIONS = "permissions",
        TABLE_NOTIFICATION = "notification";

    /**
     * @var User
     */
    private $user;

    /**
     * Prihlási uživateľa do systému
     * @param array $credentials Méno a Heslo
     * @return Identity identitu prihlaseného uživateľa pre dalšiu manipuláciu
     * @throws AuthenticationException Pri chybe pri prihlasení
     *
     */
    public function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;
        $user = $this->database->table(self::USERS_TABLE)->where(self::COLUMN_NAME, $username)->fetch();
        if (!$user) {
            throw new AuthenticationException('Zadané uživatelské jméno neexistuje.', self::IDENTITY_NOT_FOUND);
        } elseif (!Passwords::verify($password, $user[self::COLUMN_PASSWORD_HASH])) {
            throw new AuthenticationException('Zadané heslo není správně.', self::INVALID_CREDENTIAL);
        } elseif (Passwords::needsRehash($user[self::COLUMN_PASSWORD_HASH])) {
            $user->update(array(self::COLUMN_PASSWORD_HASH => Passwords::hash($password)));
        } elseif ($user->ban_login != NULL) {
            throw new  AuthenticationException('Uživateľ má login ban.');
        }

        $userData = $user->toArray();
        unset($userData[self::COLUMN_PASSWORD_HASH]);
        return new Identity($user[self::COLUMN_ID], $user[self::COLUMN_ROLE], $userData);
    }


    /**
     * Registruje nového uživateľa do systému.
     * @param ArrayHash $values Hodnoty z formu
     * @throws DuplicateNameException Ak uživateľ s uvedením menom už existuje
     */
    public function register($values)
    {
        try {
            $this->database->table(self::USERS_TABLE)->insert(array(
                self::COLUMN_NAME => $values->username,
                self::COLUMN_PASSWORD_HASH => Passwords::hash($values->password),
                self::COLUMN_ROLE => 'user',
                self::COLUMN_EMAIL => $values->email
            ));
        } catch (UniqueConstraintViolationException $e) {
            throw new DuplicateNameException;
        }
    }

    /**
     * @return \Nette\Database\Table\Selection
     * Vráti všetkých uživateľov
     */
    public function getUsers()
    {
        return $this->database->table(self::USERS_TABLE);
    }

    /**
     * @param int $id
     * @return ArrayHash bool|mixed|\Nette\Database\Table\IRow
     */
    public function getUser($id)
    {
        return $this->database->table(self::USERS_TABLE)->where(self::COLUMN_ID, $id)->fetch();
    }

    /**
     * @param $id
     * @return bool
     */
    public function checkId($id)
    {
        return $this->database->table(self::USERS_TABLE)->where(self::COLUMN_ID, $id)->fetch();
    }

    /**
     * @param $id
     * @param $values
     * @return int
     */
    public function updateUser($id, $values)
    {
        return $this->database->table(self::USERS_TABLE)->where(self::COLUMN_ID, $id)->update($values);
    }

    /**
     * @param $id
     * @return array|\Nette\Database\Table\IRow[]|\Nette\Database\Table\Selection
     */
    public function getTeamMember($id)
    {
        return $this->database->table(self::USERS_TABLE)->where('team', $id)->fetchAll();
    }

    /**
     * @param $username
     * @return mixed
     */
    public function getReceiverId($username)
    {
        $query = $this->database->table(self::USERS_TABLE)->where('username', $username)->fetch();
        return $query->id;
    }

    /**
     * @param $username
     * @return bool
     */
    public function checkReceiver($username)
    {
        return $this->database->table(self::USERS_TABLE)->where('username', $username)->fetch();
    }

    public function leaveTeam($id){
        return $this->database->table(self::USERS_TABLE)->where('id', $id)->update(array(
            'team' => NULL
        ));
    }

    public function getCountPlayersInTeam($id)
    {
        return $this->database->table(self::USERS_TABLE)->where('team', $id)->count('*');
    }

    /**
     * @param $mail
     * @return FALSE|mixed
     */
    public function getMail($mail)
    {
        return $this->database->table(self::USERS_TABLE)->where('email = ?', $mail)->fetchField('email');
    }

    public function changePassword($mail, $password)
    {
        return $this->database->table(self::USERS_TABLE)->where('email = ?', $mail)->update(array(
            'password' => Passwords::hash($password)
        ));
    }

    public function createAward($values)
    {
        return $this->database->table(self::AWARDS_TABLE)->insert($values);
    }

    public function getAwards()
    {
        return $this->database->table(self::AWARDS_TABLE)->fetchAll();
    }

    public function getAwards2()
    {
        return $this->database->table(self::AWARDS_TABLE);
    }

    public function deleteAward($id)
    {
        return $this->database->table(self::AWARDS_TABLE)->delete();
    }

    public function getProfileAwards($id)
    {
        return $this->database->table(self::AWARDS_TABLE_PROFILE)->where('users_id = ?', $id)->order('id DESC')->fetchAll();
    }

    public function insertAward($values)
    {
        return $this->database->table(self::AWARDS_TABLE_PROFILE)->insert($values);
    }

    public function getNames()
    {
        return $this->database->table(self::USERS_TABLE)->fetchPairs('id', 'username');
    }

    public function addAdmin($id)
    {
        return $this->database->table(self::USERS_TABLE)->where('id = ?', $id)->update(array(
            'role' => 'admin'
        ));
    }

    public function getAdmins()
    {
        return $this->database->table(self::USERS_TABLE)->where('role = ?', 'admin')->fetchAll();
    }

    public function deleteAdmin($id)
    {
        return $this->database->table(self::USERS_TABLE)->where('id = ?', $id)->update(array(
            'role' => 'Uživateľ'
        ));
    }

    public function deleteBanPost($id){
        return $this->database->table(self::USERS_TABLE)->where('id', $id)->update(array(
            'ban_post' => NULL,
        ));
    }

    public function deleteBanLogin($id){
        return $this->database->table(self::USERS_TABLE)->where('id', $id)->update(array(
            'ban_login' => NULL,
        ));
    }

    public function giveBanPost($id, $values){
        return $this->database->table(self::USERS_TABLE)->where('id', $id)->update(array(
            'ban_post' => $values->ban_post,
        ));
    }

    public function unbanBanPost($id){
        return $this->database->table(self::USERS_TABLE)->where('id', $id)->update(array(
            'ban_post' => NULL,
        ));
    }

    public function giveBanLogin($id, $values){
        return $this->database->table(self::USERS_TABLE)->where('id', $id)->update(array(
            'ban_login' => $values->ban_login,
        ));
    }

    public function unbanBanLogin($id){
        return $this->database->table(self::USERS_TABLE)->where('id', $id)->update(array(
            'ban_login' => NULL,
        ));
    }

    public function getNotification($id){
        return $this->database->table(self::TABLE_NOTIFICATION)->where('user_id', $id)->order('time DESC')->fetchAll();
    }

    public function insertNotification($user_id, $string){
        return $this->database->table(self::TABLE_NOTIFICATION)->insert(array(
            'user_id' => $user_id,
            'message' => $string,
        ));
    }


}

/**
 * Výnimka pre duplicitné uživatelské meno.
 * @package App\Model
 */
class DuplicateNameException extends AuthenticationException
{
    /** Konstruktor s definicích výchozí chybové zprávy. */
    public function __construct()
    {
        parent::__construct();
        $this->message = 'Uživatel s tímto jménem je již zaregistrovaný.';
    }
}