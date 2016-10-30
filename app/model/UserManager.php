<?php

namespace App\Model;

use App\Model\Entity\User;
use Nette;
use Nette\Security\Passwords;
use Kdyby\Doctrine\EntityManager;



/**
 * Users management.
 */
class UserManager extends Nette\Object implements Nette\Security\IAuthenticator
{
	const
		TABLE_NAME = 'user',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'username',
		COLUMN_PASSWORD_HASH = 'password',
		COLUMN_ROLE = 'role';

    /** @var EntityManager */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password, $type) = $credentials;

        /** @var User $user */
		$user = $this->em->getRepository('App\Model\Entity\User')->findOneByUsername($username);


		if (!$user) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

		} elseif ($type==='app' && !Passwords::verify($password, $user->getPassword())) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

        } elseif ($type==='social' && $password != $user->getPassword()) {
            throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

		} elseif (Passwords::needsRehash($user->getPassword())) {
			$user->setPassword(Passwords::hash($password));
		}


		return new Nette\Security\Identity($user->getId(),NULL, $user->toIdentity());
	}


	/**
	 * Adds new user.
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public function add($username, $password)
	{
		try {
		    $user = new User();
            $user->setUsername($username)->setPassword($password);
		    $this->em->persist($user);
            $this->em->flush();
		} catch (Nette\Database\UniqueConstraintViolationException $e) {
			throw new DuplicateNameException;
		}
	}

    function toArray($obj)
    {
        if (is_object($obj)) $obj = (array)$obj;
        if (is_array($obj)) {
            $new = array();
            foreach ($obj as $key => $val) {
                $new[$key] = self::toArray($val);
            }
        } else {
            $new = $obj;
        }

        return $new;
    }

}



class DuplicateNameException extends \Exception
{}
