<?php
/**
 * JOSSO Agent class definition.
 *
 * @package org.josso.agent.php
 */

/**
JOSSO: Java Open Single Sign-On

Copyright 2004-2008, Atricore, Inc.

This is free software; you can redistribute it and/or modify it
under the terms of the GNU Lesser General Public License as
published by the Free Software Foundation; either version 2.1 of
the License, or (at your option) any later version.

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this software; if not, write to the Free
Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA
02110-1301 USA, or see the FSF site: http://www.fsf.org.

*/

/**
 * Include NUSOAP soap client.
 */
require_once('nusoap/nusoap.php');

/**
 * PHP Josso Agent implementation based on WS.
 *
 * @package  org.josso.agent.php
 *
 * @author Sebastian Gonzalez Oyuela <sgonzalez@josso.org>
 * @version $Id: class.jossoagent.php 613 2008-08-26 16:42:10Z sgonzalez $
 * @author <a href="mailto:sgonzalez@josso.org">Sebastian Gonzalez Oyuela</a>
 *
 */
class jossoagent  {


	// ---------------------------------------
	// JOSSO Agent configuration : 
	// --------------------------------------- 
	
	/**
	 * WS End-point
	 * @var string
	 * @access private
	 */
	var $endpoint = 'http://localhost:8080';
	
	/**
	 * SSOSessionManager service path
	 * @var string
	 * @access private
	 */
	var $sessionManagerServicePath = '/josso/services/SSOSessionManagerSoap';
	
	/**
	 * SSOIdentityManager service path
	 * @var string
	 * @access private
	 */
	var $identityManagerServicePath = '/josso/services/SSOIdentityManagerSoap';
	
	/**
	 * SSOIdentityProvider service path
	 * @var string
	 * @access private
	 */
	var $identityProviderServicePath = '/josso/services/SSOIdentityProviderSoap';
	
	/**
	 * WS Proxy Settings
     * @var string
     * @access private
     */
	var $proxyhost = '';

	/**
     * @var string
     * @access private
     */
	var $proxyport = '';

	/**
     * @var string
     * @access private
     */
	var $proxyusername = '';

	/**
     * @var string
     * @access private
     */
	var $proxypassword = '';
	
	// Gateway
    /**
     * @var string
     * @access private
     */
	var $gatewayLoginUrl;

	/**
     * @var string
     * @access private
     */
	var $gatewayLogoutUrl;

	/**
     * @var string
     * @access private
     */
	var $sessionAccessMinInterval = 1000;

	/**
	 * Base path where JOSSO pages  can be found, like josso-security-check.php
	 */
	var $baseCode ;

	/**
	 * MS P3P HTTP Header value, for IFRAMES compatibility with IE 6+
	 */
	var $p3pHeaderValue;

	// ---------------------------------------
	// JOSSO Agent internal state : 
	// --------------------------------------- 

	/**
	 * SOAP Clienty for identity mgr.
     * @var string
     * @access private
     */
	var $identityMgrClient;


	/**
	 * SOAP Clienty for identity provider.
     * @var string
     * @access private
     */
	var $identityProviderClient;

	
	/**
	 * SOAP Clienty for session mgr.
     * @var string
     * @access private
     */
	var $sessionMgrClient;
	
	/**
	 * Last occurred error
     * @var string
     * @access private
     */
	var $fault;

	/**
	 * Last occurred fault
     * @var string
     * @access private
     */
	var $err;
	
	/**
	 * @return jossoagent a new Josso PHP Agent instance.
	 */
	function getNewInstance() {
		// Get config variable values from josso.inc.
		global $josso_gatewayLoginUrl, $josso_gatewayLogoutUrl, $josso_endpoint, $josso_proxyhost, $josso_proxyport, 
						$josso_proxyusername, $josso_proxypassword, $josso_agentBasecode, $josso_p3pHeaderValue, 
						$josso_sessionManagerServicePath, $josso_identityManagerServicePath, $josso_identityProviderServicePath;
		
		return new jossoagent($josso_gatewayLoginUrl, 
							  $josso_gatewayLogoutUrl, 
							  $josso_endpoint, 
							  $josso_proxyhost, 
							  $josso_proxyport, 
							  $josso_proxyusername, 
							  $josso_proxypassword,
							  $josso_agentBasecode,
							  $josso_p3pHeaderValue,
							  $josso_sessionManagerServicePath,
							  $josso_identityManagerServicePath,
							  $josso_identityProviderServicePath);
	}
	
	/**
	* constructor
	*
	* @access private
	*
	* @param    string $josso_gatewayLoginUrl 
	* @param    string $josso_gatewayLogoutUrl 
	* @param    string $josso_endpoint SOAP server
	* @param    string $josso_proxyhost
	* @param    string $josso_proxyport
	* @param    string $josso_proxyusername
	* @param    string $josso_proxypassword
	*/
	function jossoagent($josso_gatewayLoginUrl, $josso_gatewayLogoutUrl, $josso_endpoint, 
						$josso_proxyhost, $josso_proxyport, $josso_proxyusername, $josso_proxypassword, $josso_agentBasecode, $josso_p3pHeaderValue, 
						$josso_sessionManagerServicePath, $josso_identityManagerServicePath, $josso_identityProviderServicePath) {
	
		// WS Config
		$this->endpoint = $josso_endpoint;
		$this->proxyhost = $josso_proxyhost;
		$this->proxyport = $josso_proxyport;
		$this->proxyusername = $josso_proxyusername;
		$this->proxypassoword = $josso_proxypassword;
		$this->baseCode = $josso_agentBasecode;
		
		// Agent config
		$this->gatewayLoginUrl = $josso_gatewayLoginUrl;
		$this->gatewayLogoutUrl = $josso_gatewayLogoutUrl;


		// Others
		$this->p3pHeaderValue = $josso_p3pHeaderValue;
		
		if (isset($josso_sessionAccessMinInterval)) {
			$this->sessionAccessMinInterval = $josso_sessionAccessMinInterval;
		}
		
		if (isset($josso_sessionManagerServicePath)) {
			$this->sessionManagerServicePath = $josso_sessionManagerServicePath;
		}
		
		if (isset($josso_identityManagerServicePath)) {
			$this->identityManagerServicePath = $josso_identityManagerServicePath;
		}
		
		if (isset($josso_identityProviderServicePath)) {
			$this->identityProviderServicePath = $josso_identityProviderServicePath;
		}								
	}
	
	/**
	* Gets the authnenticated jossouser, if any.
	*
	* @return jossouser the authenticated user information.
	* @access public
	*/
	function getUserInSession() {
	
		$sessionId = $this->getSessionId();
		if (!isset($sessionId)) {
			return ;
		}

		// SOAP Invocation
		$soapclient = $this->getIdentityMgrSoapClient();

		$findUserInSessionRequest = array('FindUserInSession' => array('ssoSessionId' => $sessionId));
		$findUserInSessionResponse  = $soapclient ->call('findUserInSession', $findUserInSessionRequest, 
						'urn:org:josso:gateway:ws:1.2:protocol', '', false, null, 'document', 'literal');
		
		if (! $this->checkError($soapclient)) {
			return $this->newUser($findUserInSessionResponse['SSOUser']);
		}
		
	}
	
	/**
	* Returns true if current authenticated user is associated to the received role.
	* If no user is logged in, returns false.
    *
	* @param string $rolename the name of the role.
	*
	* @return bool
	* @access public
	*/
	function isUserInRole($rolename) {
		$user = $this->getUserInSession();
		$sessionId = $this->getSessionId();
		if (!isset($sessionId)) {
			return FALSE;
		}

		$roles = $this->findRolesBySSOSessionId($sessionId) ;
		
		foreach($roles as $role) {
			if ($role->getName() == $rolename) 
				return TRUE;
		}
		return FALSE;
	}

	/**
	* Returns all roles associated to the given username.
	*
	* @deprecated use findRolesBySSOSessionId
	* @return jossorole[] an array with all jossorole instances
	* @access public
	*/
	function findRolesBySSOSessionId ($sessionId) {
	
		// SOAP Invocation
		$soapclient = $this->getIdentityMgrSoapClient();
		$findRolesBySSOSessionIdRequest = array('FindRolesBySSOSessionId' => array('ssoSessionId' => $sessionId));

		$findRolesBySSOSessionIdResponse = $soapclient->call('findRolesBySSOSessionId', $findRolesBySSOSessionIdRequest, 
						'urn:org:josso:gateway:ws:1.2:protocol', '', false, null, 'document', 'literal');

		if (! $this->checkError($soapclient)) {
			// Build array of roles
			$i = 0;
			$result = $findRolesBySSOSessionIdResponse['roles'];

			foreach($result as $roledata) {
				$roles[$i] = $this->newRole($roledata);
				$i++;
			}
			return $roles;
		}
		
	}
	
	/**
	 * Sends a keep-alive notification to the SSO server so that SSO sesison is not lost.
	 * @access public
	 */
	function accessSession() {
	
		// Check if a session ID is pressent.
		$sessionId = $this->getSessionid();
		if (!isset($sessionId ) || $sessionId == '') {
			return '';
		}

		// Check last access time :
		// $lastAccessTime = $_SESSION['JOSSO_LAST_ACCESS_TIME'];
		// $now = time();

		// Assume that _SESSION is set.
        $soapclient = $this->getSessionMgrSoapClient();

        $accessSessionRequest = array('AccessSession' => array('ssoSessionId' => $sessionId));
        $accessSessionResponse  = $soapclient->call('accessSession', $accessSessionRequest, 
        				'urn:org:josso:gateway:ws:1.2:protocol', '', false, null, 'document', 'literal');

        if ($this->checkError($soapclient)) {
            return '';
        }

        return $accessSessionResponse['ssoSessionId'];
		
	}

	function isAutomaticLoginRequired() {

        // TODO : This is not the best way to avoid loops when no referer is present, the flag should expire and
        // should not be attached to the SSO Session

        // The first time we access a partner application, we should attempt an automatic login.
        $autoLoginExecuted = $_SESSION["JOSSO_AUTOMATIC_LOGIN_EXECUTED"];
        // If no referer host is found but we did not executed auto login yet, give it a try.
        if (!isset($autoLoginExecuted)) {
            $_SESSION["JOSSO_AUTOMATIC_LOGIN_EXECUTED"] = TRUE;
            return TRUE;
        }

        if (isset($_SERVER['HTTP_REFERER']))
            $referer = $_SERVER['HTTP_REFERER'];

        // If we have a referer host that differs from our we require an autologinSSs
        if (isset($referer)) {

// GEOPASS FIX

			/*
            $oldReferer = $_SESSION["JOSSO_AUTOMATIC_LOGIN_REFERER"];
            if (isset($oldReferer)) {
                unset($_SESSION["JOSSO_AUTOMATIC_LOGIN_REFERER"]);
                return FALSE;
            }
            */


            if (isset($_SESSION["JOSSO_AUTOMATIC_LOGIN_REFERER"])) {
                unset($_SESSION["JOSSO_AUTOMATIC_LOGIN_REFERER"]);
                return FALSE;
            }


            $protocol = 'http';
            $host = $_SERVER['HTTP_HOST'];

            if (isset($_SERVER['HTTPS'])) {

                // This is a secure connection, the default PORT is 443
                $protocol = 'https';
                if ($_SERVER['SERVER_PORT'] != 443) {
                    $port = $_SERVER['SERVER_PORT'];
                }

            } else {
                // This is a NON secure connection, the default PORT is 80
                $protocol = 'http';
                if ($_SERVER['SERVER_PORT'] != 80) {
                    $port = $_SERVER['SERVER_PORT'];
                }
            }

            $baseUrl = $protocol.'://'.$host.(isset($port) ? ':'.$port : '');

            if (strncmp($referer, $baseUrl, strlen($baseUrl) != 0)) {

                // Store referer for future reference!
                $_SESSION["JOSSO_AUTOMATIC_LOGIN_REFERER"] = $referer;
                return TRUE;
            }
        } else {
            if (isset($_SESSION["JOSSO_AUTOMATIC_LOGIN_REFERER"])) {
                $oldReferer = $_SESSION["JOSSO_AUTOMATIC_LOGIN_REFERER"];
                if (isset($oldReferer)  && strcmp($oldReferer, "NO_REFERER") != 0) {
                    unset($_SESSION["JOSSO_AUTOMATIC_LOGIN_REFERER"]);
                    return FALSE;
                } else {
                    $_SESSION["JOSSO_AUTOMATIC_LOGIN_REFERER"] = "NO_REFERER";
                    return TRUE;
                }
            }
        }

        return FALSE;

    }
	
	/**
	 * Returns the URL where the user should be redireted to authenticate.
	 *
	 * @return string the configured login url.
	 *
	 * @access public
	 */
	function getGatewayLoginUrl() {
		return $this->gatewayLoginUrl;
	}

	/**
	 * Returns the SSO Session ID given an assertion id.
	 *
	 * @param string $assertionId
	 *
	 * @return string, the SSO Session associated with the given assertion.
	 *
	 * @access public
	 */
	function resolveAuthenticationAssertion($assertionId) {
		// SOAP Invocation
		$soapclient = $this->getIdentityProvdierSoapClient();

        $resolveAuthenticationAssertionRequest = array('ResolveAuthenticationAssertion' => array('assertionId' => $assertionId));
        $resolveAuthenticationAssertionResponse = $soapclient->call('resolveAuthenticationAssertion', $resolveAuthenticationAssertionRequest, 
        				'urn:org:josso:gateway:ws:1.2:protocol', '', false, null, 'document', 'literal');
        
		if (! $this->checkError($soapclient)) {
			// Return SSO Session ID
			return $resolveAuthenticationAssertionResponse['ssoSessionId'];
		}

	}
	
	/**
	 * Returns the URL where the user should be redireted to logout.
	 *
     * @return string the configured logout url.
     *
     * @access public
	 */
	function getGatewayLogoutUrl() {
		return $this->gatewayLogoutUrl;
	}

	/**
	 * Returns the base path where JOSSO code is stored.
	 */
	function getBaseCode() {
	    return $this->baseCode;
    }

	/**
	 * Returns P3P header value
	 */
	function getP3PHeaderValue() {
	    return $this->p3pHeaderValue;
    }

	/**
	 * Allows client applications to access error messages
	 *
	 * @access public
	 */
	function getError() {
		return $this->err;
	}
	
	/**
	 * Allows client applications to access error messages
	 *
	 * @access public
	 */
	function getFault() {
		return $this->fault;
	}
	
	//----------------------------------------------------------------------------------------
	// Protected methods intended to be invoked only within this class or subclasses.
	//----------------------------------------------------------------------------------------
	
	/**
	 * Gets current JOSSO session id, if any.
	 *
	 * @access private
	 */
	function getSessionId() {
	    if (isset($_COOKIE['JOSSO_SESSIONID']))
		    return $_COOKIE['JOSSO_SESSIONID'];
	}
	
	/**
	 * Factory method to build a user from soap data.
	 *
	 * @param array user information as received from WS.
	 * @return jossouser a new jossouser instance.
	 *
	 * @access private
	 */
	function newUser($data) {
		// Build a new jossouser 
		$username = $data['name'];
		$properties = $data['properties'];

		$user = new jossouser($username, $properties);
		
		return $user;
	}
	
	/**
	 * Factory method to build a role from soap data.
	 *
	 * @param array role information as received from WS.
	 * @return jossorole a new jossorole instance
	 *
	 * @access private
	 */
	function newRole($data) {
		// Build a new jossorole
		$rolename = $data['!name'];
		$role = new jossorole($rolename);
		return $role;
	}
	
	/**
	 * Checks if an error occured with the received soapclient and stores information in agent state.
	 *
	 * @access private
	 */
	function checkError($soapclient) {
		// Clear old error/fault information.
		unset($this->fault);				
		unset($this->err);

		// Check for a fault
		if ($soapclient->fault) {
			$this->fault = $soapclient->fault;
			return TRUE;
		} else {
			// Check for errors
			if ($soapclient->error_str != '') {
			    $this->err = $soapclient->error_str;
				return TRUE;
			} 
		}
		
		// No errors ...
		return FALSE;
	
	}
	
	/**
	 * Gets the soap client to access identity service.
	 *
	 * @access private
	 */
	function getIdentityMgrSoapClient() {
		// Lazy load the propper soap client
		if (!isset($this->identityMgrClient)) {
			$this->identityMgrClient = new nusoap_client($this->endpoint . $this->identityManagerServicePath, false,
											$this->proxyhost, $this->proxyport, $this->proxyusername, $this->proxypassword);

            // Sets default encoding to UTF-8 ...
            $this->identityMgrClient->soap_defencoding = 'UTF-8';
            $this->identityMgrClient->decodeUTF8(false);
		}
		return $this->identityMgrClient;
	}

	/**
	 * Gets the soap client to access identity provider.
	 *
	 * @access private
	 */
	function getIdentityProvdierSoapClient() {
		// Lazy load the propper soap client
		if (!isset($this->identityProviderClient)) {
			$this->identityProviderClient = new nusoap_client($this->endpoint . $this->identityProviderServicePath, false,
											$this->proxyhost, $this->proxyport, $this->proxyusername, $this->proxypassword);

            // Sets default encoding to UTF-8 ...
            $this->identityProviderClient->soap_defencoding = 'UTF-8';
            $this->identityProviderClient->decodeUTF8(false);
		}
		return $this->identityProviderClient;
	}

	
	/**
	 * Gets the soap client to access session service.
	 *
	 * @access private
	 */
	function getSessionMgrSoapClient() {
		// Lazy load the propper soap client
		if (!isset($this->sessionMgrClient)) {
			// SSOSessionManager SOAP Client
			$this->sessionMgrClient = new nusoap_client($this->endpoint . $this->sessionManagerServicePath, false,
										$this->proxyhost, $this->proxyport, $this->proxyusername, $this->proxypassword);
		}
		return $this->sessionMgrClient;

	}

}
?>
