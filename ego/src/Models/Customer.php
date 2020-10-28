<?php

namespace Ego\Models;

use Ego\Providers\Util;
use Ego\Struct\CustomerRowStruct;

class Customer extends BaseModel {

	/**
	 * Set Newsletter status
	 *
	 * @param int $customerId
	 * @param bool $newsletter
	 * @return bool
	 */
	public function setNewsletter(int $customerId, bool $newsletter) {
		$sql = "
			UPDATE oc_customer
			SET
				newsletter = :newsletter
			
			WHERE 1
				  AND customer_id = :customer_id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':newsletter', $newsletter ? 1 : 0, \PDO::PARAM_INT);
		$dataQuery->bindValue(':customer_id', $customerId, \PDO::PARAM_INT);
		return $dataQuery->execute();
	}

	/**
	 * Add row
	 *
	 * @param CustomerRowStruct $row
	 * @return string
	 */
	public function add(CustomerRowStruct $row) {
		$sql = "
			INSERT INTO oc_customer
			SET
				customer_group_id = :customer_group_id,
				store_id          = :store_id,
				language_id       = :language_id,
				firstname         = :firstname,
				lastname          = :lastname,
				email             = :email,
				telephone         = :telephone,
				fax               = :fax,
				password          = :password,
				salt              = :salt,
				cart              = :cart,
				wishlist          = :wishlist,
				newsletter        = :newsletter,
				address_id        = :address_id,
				region			  = :region,
				city			  = :city,
				warehouse		  = :warehouse,
				custom_field      = :custom_field,
				ip                = :ip,
				status            = :status,
				safe              = :safe,
				token             = :token,
				code              = :code,
				date_added        = NOW(),
				updated 		  = 1
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':customer_group_id', $row->getCustomerGroupId(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':store_id', $row->getStoreId(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':language_id', $row->getLanguageId(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':firstname', $row->getFirstName(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':lastname', $row->getLastName(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':email', $row->getEmail(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':telephone', $row->getTelephone(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':fax', $row->getFax(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':password', $row->getPassword(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':salt', $row->getSalt(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':cart', $row->getCart(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':wishlist', $row->getWishList(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':newsletter', $row->getNewsletter(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':address_id', $row->getAddressId(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':region', $row->getRegion(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':city', $row->getCity(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':warehouse', $row->getWarehouse(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':custom_field', $row->getCustomerField(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':ip', $row->getIp(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':status', $row->getStatus(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':safe', $row->getSafe(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':token', $row->getToken(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':code', $row->getCode(), \PDO::PARAM_STR);
		$dataQuery->execute();

		return $this->_getDb()->lastInsertId();
	}

	/**
	 * Update row
	 *
	 * @param CustomerRowStruct $row
	 * @return bool
	 */
	public function update(CustomerRowStruct $row) {
		$sql = "
			UPDATE oc_customer
			SET
				customer_group_id = :customer_group_id,
				store_id          = :store_id,
				language_id       = :language_id,
				firstname         = :firstname,
				lastname          = :lastname,
				email             = :email,
				telephone         = :telephone,
				fax               = :fax,
				cart              = :cart,
				wishlist          = :wishlist,
				newsletter        = :newsletter,
				address_id        = :address_id,
				region			  = :region,
				city			  = :city,
				warehouse		  = :warehouse,
				custom_field      = :custom_field,
				ip                = :ip,
				status            = :status,
				safe              = :safe,
				token             = :token,
				code              = :code,
				date_added        = NOW(),
				updated 		  = 1
			WHERE 1
				AND customer_id = :customer_id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':customer_group_id', $row->getCustomerGroupId(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':store_id', $row->getStoreId(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':language_id', $row->getLanguageId(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':firstname', $row->getFirstName(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':lastname', $row->getLastName(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':email', $row->getEmail(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':telephone', $row->getTelephone(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':fax', $row->getFax(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':cart', $row->getCart(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':wishlist', $row->getWishList(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':newsletter', $row->getNewsletter(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':address_id', $row->getAddressId(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':region', $row->getRegion(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':city', $row->getCity(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':warehouse', $row->getWarehouse(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':custom_field', $row->getCustomerField(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':ip', $row->getIp(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':status', $row->getStatus(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':safe', $row->getSafe(), \PDO::PARAM_INT);
		$dataQuery->bindValue(':token', $row->getToken(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':code', $row->getCode(), \PDO::PARAM_STR);
		$dataQuery->bindValue(':customer_id', $row->getCustomerId(), \PDO::PARAM_INT);

		return $dataQuery->execute();
	}

	/**
	 * Update password
	 *
	 * @param int $id
	 * @param string $password
	 * @param string $salt
	 * @return bool
	 */
	public function updatePassword(int $id, string $password, string $salt) {
		$sql = "
			UPDATE oc_customer
			SET
				password = :password,
				salt 	 = :salt
			
			WHERE 1
				AND customer_id = :customer_id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':password', $password, \PDO::PARAM_STR);
		$dataQuery->bindValue(':salt', $salt, \PDO::PARAM_STR);
		$dataQuery->bindValue(':customer_id', $id, \PDO::PARAM_INT);

		return $dataQuery->execute();
	}

	/**
	 * Return customer by ID
	 *
	 * @param int $id
	 * @param bool|null $isStruct
	 * @return CustomerRowStruct|mixed|null
	 */
	public function get(int $id, bool $isStruct = null) {
		$sql = "
			SELECT
			
				customer_id,
				customer_group_id,
				store_id,
				language_id,
				firstname,
				lastname,
				email,
				telephone,
				fax,
				password,
				salt,
				cart,
				wishlist,
				newsletter,
				address_id,
				region,
				city,
				warehouse,
				custom_field,
				ip,
				status,
				safe,
				token,
				code,
				date_added = NOW()
			
			FROM oc_customer oc
			
			WHERE 1
				  AND oc.customer_id = :customer_id
			";

		$dataQuery = $this->_getDb()->prepare($sql);
		$dataQuery->bindValue(':customer_id', $id, \PDO::PARAM_INT);
		$dataQuery->execute();

		$data = $dataQuery->fetch();

		if (empty($data)) {
			return null;
		}

		if ($isStruct) {
			return (new CustomerRowStruct())
				->setCustomerId((int)Util::getArrItem($data, 'customer_id'))
				->setCustomerGroupId((int)Util::getArrItem($data, 'customer_group_id'))
				->setStoreId((int)Util::getArrItem($data, 'store_id'))
				->setLanguageId((int)Util::getArrItem($data, 'language_id'))
				->setFirstName(Util::getArrItem($data, 'firstname', ''))
				->setLastName(Util::getArrItem($data, 'lastname', ''))
				->setEmail(Util::getArrItem($data, 'email', ''))
				->setTelephone(Util::getArrItem($data, 'telephone', ''))
				->setFax(Util::getArrItem($data, 'fax', ''))
				->setPassword(Util::getArrItem($data, 'password', ''))
				->setSalt(Util::getArrItem($data, 'salt', ''))
				->setCart(Util::getArrItem($data, 'cart', ''))
				->setWishList(Util::getArrItem($data, 'wishlist', ''))
				->setNewsletter((int)Util::getArrItem($data, 'newsletter'))
				->setAddressId((int)Util::getArrItem($data, 'address_id'))
				->setRegion(Util::getArrItem($data, 'region', ''))
				->setCity(Util::getArrItem($data, 'city', ''))
				->setWarehouse(Util::getArrItem($data, 'warehouse', ''))
				->setCustomerField(Util::getArrItem($data, 'custom_field', ''))
				->setIp(Util::getArrItem($data, 'ip', ''))
				->setStatus((int)Util::getArrItem($data, 'status'))
				->setSafe((int)Util::getArrItem($data, 'safe'))
				->setToken(Util::getArrItem($data, 'token', ''))
				->setCode(Util::getArrItem($data, 'code', ''))
				->setDateAdded(Util::getArrItem($data, 'date_added', ''));
		}

		return $data;
	}

}
