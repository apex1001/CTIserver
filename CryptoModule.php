<?php

	/**
	 * Crypto module. Based on the information from:
	 * 
	 * http://nl3.php.net/mcrypt
	 * http://stackoverflow.com/questions/11873878/c-sharp-encryption-to-php-decryption
	 * 
	 * @author V. Vogelesang
	 * 
	 */
	class CryptoModule {	
		
		private $key;
		private $iv;
	
		public function __construct($key, $iv)
		{
			$this->key = $key;
			$this->iv = $iv;
		}
			
		
		/**
		 * Decrypt 128 Bit Base64 encoded String
		 * 
		 * @param cipherText
		 * @return plaintext
		 * 
		 */
		function decryptRJ128($cipherText)
		{
			$cipherText = base64_decode($cipherText);
			return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $cipherText, MCRYPT_MODE_CBC, $this->iv));			
		}
		
		/**
		 * Encrypt string to 128 Bit Base64
		 *
		 * @param plaintext
		 * @return ciphertext
		 *
		 */
		function encryptRJ128($plaintext)
		{
			$cipherText = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $plaintext, MCRYPT_MODE_CBC, $this->iv);
			return base64_encode($cipherText);
		}	
	}
