<?php
/**
 * Google Analytics integration in OXID
 *
 * Copyright (c) 2013 Paul Lamp | paul-lamp.de
 * E-mail: pl@paul-lamp.de
 * http://www.paul-lamp.de
 * 
 * based on
 * Piwik integration in OXID
 *
 * Copyright (c) 2011 Joscha Krug | marmalade.de
 * E-mail: mail@marmalade.de
 * http://www.marmalade.de
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */
 
 class pl_analytics {

    const VERSION = '0.1';

    const CONFIG_ENTRY_NAME = 'pl_analytics_config';

    protected $_aPushParams = array();
	
	//eCommerce Data
	protected $_aTransactionParams = array();
	protected $_aECommerceItems = array();
	
    protected $_aConfig = array(
        'ga_ua_id' => array(
            'value'=> 'UA-XXXXXXXX-X',
            'input_type' => 'text',
        ),
        'ga_domain' => array(
            'value'=> 'domain.tld',
            'input_type' => 'text',
        ),
        
      );

    public function __construct()
    {
        $this->_loadConfig();
    }

    /**
     * returns current version of pl_analytics class
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    protected function _loadConfig()
    {
        $aSavedConfig = oxRegistry::getConfig()->getShopConfVar(self::CONFIG_ENTRY_NAME);
        if ($aSavedConfig && count($aSavedConfig) == count($this->_aConfig)) {
            $this->_aConfig = $aSavedConfig;
        }
        else {
            $this->_saveConfig();
        }

    }

    protected function _saveConfig()
    {
        oxRegistry::getConfig()->saveShopConfVar( 'arr', self::CONFIG_ENTRY_NAME, $this->_aConfig );
    }

    public function getConfig()
    {
        return $this->_aConfig;
    }

    public function changeConfig($aNewValues)
    {
        $blChanged = false;
        foreach ($aNewValues as $sKey => $sNewValue) {
            if (isset($this->_aConfig[$sKey])) {
                $blChanged = true;
                $this->_aConfig[$sKey]['value'] = $sNewValue;
            }
        }
        if ($blChanged) {
            $this->_saveConfig();
        }
        return $blChanged;
    }

    /**
     * returns current/active page controller (aka view)
     * @return oxUBase
     */
    protected function _getViewOrder()
    {
        return oxRegistry::getConfig()->getActiveView();
    }

    /**
     * Saves given values for pushing to javascript array object  (_gaq.push)
     * @return void
     */
    public function addPushParams()
    {
        if (func_num_args() < 1) {
            return;
        }
        $this->_aPushParams[] = func_get_args();
    }

    /**
     * returns javascript array pushes, that are saved
     * before with addPushParams("param1", "param2").
     * example:
     * _gaq.push(["func1", false, "param2", 1, 0.2]);
     * _gaq.push(["func2"]);
     * @return string
     */
    public function generateParams()
    {
        $sReturn = '';
        foreach ($this->_aPushParams as $aPushArray) {
            $aFormed = array();
            foreach($aPushArray as $mPushParam) {
                if (is_string($mPushParam)) {
					$pattern=array();
					$pattern[0]='#(?<!\\\\)"#';
					$pattern[1]='#&quot;#';
					$replacement=array();
					$replacement[0]='\"';
					$replacement[1]='\"';
                    $aFormed[] = '"'.preg_replace($pattern, $replacement, $mPushParam).'"';
				
                }
                elseif(is_bool($mPushParam)) {
                    $aFormed[] = $mPushParam?'true':'false';
                }
                elseif(is_double($mPushParam) || is_float($mPushParam)) {
                    $aFormed[] = sprintf("%.2f", $mPushParam);
                }
                elseif(is_array($mPushParam) && isset($mPushParam['type']) && $mPushParam['type'] == 'raw') {
                    $aFormed[] = $mPushParam['value'];
                }
                else {
                    $aFormed[] = $mPushParam;
                }
            }
            $sReturn .="\nga(".implode(', ', $aFormed).");";
        }
        return $sReturn;
    }

    public function getConfigValue($sValue) {
        if (isset($this->_aConfig[$sValue]) && isset($this->_aConfig[$sValue]['value'])) {
            return $this->_aConfig[$sValue]['value'];
        }
    }

    /**
     * returns config parameter for Google Analytics UA-Tracking-Id
     * @return string
     */
    public function getGaUaId()
    {
        return $this->getConfigValue('ga_ua_id');
    }

    /**
     * returns config parameter for Google Analytics Website URL
     * @return string
     */
    public function getGaDomain()
    {
        $sDomain = "auto";
        if($this->getConfigValue('ga_domain') != "domain.tld") {
            $sDomain = $this->getConfigValue('ga_domain');
        }
        return $sDomain;
    }

    /**
     * @param oxBasket $oBasket
     * @return void
     */
    protected function _setEcommerceItemsByBasket($oBasket,$sOrderNr)
    {
        foreach ($oBasket->getContents() as $oBasketItem)
        {
            $oArticle = $oBasketItem->getArticle();
            $this->addPushParams(
                '_addItem',
                $sOrderNr,
                $oArticle->oxarticles__oxartnum->value,
                $oArticle->oxarticles__oxtitle->value,
                $oArticle->getCategory()->oxcategories__oxtitle->value,
                number_format($oBasketItem->getUnitPrice()->getNettoPrice(),2),
                number_format($oBasketItem->getAmount(),0)
            );
        }
    }
	
    /**
     * @param order $oViewObject
     * @return void
     */
    public function setGaParamsForOrder($oViewObject)
    {			
		$my_Basket     = $oViewObject->getBasket();
		$my_BruttoSum  = $my_Basket->getBruttoSum();		
		$my_ViewConfig = $oViewObject->getViewConfig();		
				
		// GA Values:		
		/* GA_TransactionID   */ $my_sid       = $my_ViewConfig->getSessionId();
		/* GA_Total           */ $my_NettoSum  = number_format($my_Basket->getNettoSum(),2);
		/* GA_Tax             */ $my_TaxAmount = number_format($my_BruttoSum - $my_NettoSum,2);
		/* GA_Shipping        */ $my_delCost   = number_format($my_Basket->getDeliveryCosts(),2);
        /* GA_ShippingCity    */ $my_City      = $oViewObject->getUser()->oxuser__oxcity->value;  		
        /* GA_ShippingState   */ $my_State     = ''; 		
        /* GA_ShippingCountry */ $my_Country   = $oViewObject->getUser()->oxuser__oxcountry->value;


		$ga_TransactionArray = Array(		
            'GA_Method'          => '_addTrans',
            'GA_TransactionID'   => $my_sid,
            'GA_Store'           => $this->getGaDomain(),
            'GA_Total'		     => $my_NettoSum,
            'GA_Tax'             => $my_TaxAmount,
            'GA_Shipping'	     => $my_delCost,
            'GA_ShippingCity'    => $my_City,
            'GA_ShippingState'   => $my_State,
            'GA_ShippingCountry' => $my_Country						
		);
		
		$_SESSION['GA_OrderParams'] = $ga_TransactionArray;
	}

    /**
     * @param Thankyou $oViewObject
     * @return void
     */
    public function setGaParamsForThankyou($oViewObject)
    {	
		if (isset($_SESSION['GA_OrderParams']))
		{
			$this->addPushParams('require', 'ecommerce', 'ecommerce.js');
			
			$my_gaTransactionArray = $_SESSION['GA_OrderParams'];
			
			$this->addPushParams(
				$my_gaTransactionArray['GA_Method'],
				$my_gaTransactionArray['GA_TransactionID'],
				$my_gaTransactionArray['GA_Store'],
				$my_gaTransactionArray['GA_Total'],
				$my_gaTransactionArray['GA_Tax'],
				$my_gaTransactionArray['GA_Shipping'],
				$my_gaTransactionArray['GA_ShippingCity'],
				$my_gaTransactionArray['GA_ShippingState'],
				$my_gaTransactionArray['GA_ShippingCountry']
			);

			$this->_aTransactionParams['id']          = $my_gaTransactionArray['GA_TransactionID'];
			$this->_aTransactionParams['affiliation'] = $my_gaTransactionArray['GA_Store'];
			$this->_aTransactionParams['revenue']     = ($my_gaTransactionArray['GA_Total'] + $my_gaTransactionArray['GA_Tax'] + $my_gaTransactionArray['GA_Shipping']);
			$this->_aTransactionParams['shipping']    = $my_gaTransactionArray['GA_Shipping'];
			$this->_aTransactionParams['tax']         = $my_gaTransactionArray['GA_Tax'];			

			//$this->_setEcommerceItemsByBasket($oBasket,$oOrder->oxorder__oxordernr->value);
			//$this->addPushParams('ecommerce:send');		
			//$this->addPushParams('_trackTrans');		
		}
    }

    /**
     * runs function setGAParamsFor.. by active controller (_getViewOrder())
     * if function "tobasket" called, setGaParamsForBasket() executes
     * @return void
     */
    protected function _setGaParamsByViewObject()
    {
        $oViewObject = $this->_getViewOrder();
        $oRefClass = new ReflectionClass($this);
        $sFuncName = 'setGaParamsFor'.ucfirst($oViewObject->getClassName());
        if($oRefClass->hasMethod($sFuncName)) {
            $this->$sFuncName($oViewObject);
        }
/*
        if ($oViewObject->getFncName() == 'tobasket') {
            $this->setGaParamsForBasket($oViewObject);
        }
*/
    }

    /**
     * returns HTML string with Google Analytics javascript source and params
     * @return string
     */
    public function getPlAnalyticsCode()
    {

        $sPlAnalyticsCode = "<!-- Google Analytics Plugin pl_analytics by paul-lamp.de, sponsored by www.Fit-im-Sport.de -->\n";
        $sPlAnalyticsCode .= '<script type="text/javascript">' . "\n";
		$sPlAnalyticsCode .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){" . "\n";
		$sPlAnalyticsCode .= "(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o)," . "\n";
		$sPlAnalyticsCode .= "m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)" . "\n";
		$sPlAnalyticsCode .= "})(window,document,'script','//www.google-analytics.com/analytics.js','ga');" . "\n";
                
        $this->addPushParams('set', 'anonymizeIp', 'true');
        $this->addPushParams('create', $this->getGaUaId(), $this->getGaDomain());
		$this->addPushParams('send', 'pageview');
        $this->_setGaParamsByViewObject();

		$sPlAnalyticsCode .= $this->generateParams() . "\n";
		
		if($myActiveClass=='thankyou' && count($this->_aTransactionParams)>=4)
		{		
			$sPlAnalyticsCode .=	"" . "\n";
			$sPlAnalyticsCode .=	"ga('ecommerce:addTransaction', {" . "\n";
			$sPlAnalyticsCode .=	"'id':          '" . $this->_aTransactionParams['id']          . "'," . "\n";	// Transaction ID. Required.
			$sPlAnalyticsCode .=	"'affiliation': '" . $this->_aTransactionParams['affiliation'] . "'," . "\n";	// Affiliation or store name.
			$sPlAnalyticsCode .=	"'revenue':     '" . $this->_aTransactionParams['revenue']     . "'," . "\n";	// Grand Total.
			$sPlAnalyticsCode .=	"'shipping':    '" . $this->_aTransactionParams['shipping']    . "'," . "\n";	// Shipping.
			$sPlAnalyticsCode .=	"'tax':         '" . $this->_aTransactionParams['tax']         . "'," . "\n";	// Tax.
			$sPlAnalyticsCode .=	"'currency':    'EUR'"                                                . "\n";	// EUR.
			$sPlAnalyticsCode .=	"});" . "\n";
			$sPlAnalyticsCode .=	"" . "\n";	
			
		$sPlAnalyticsCode .= "ga('ecommerce:send');" . "\n";
		}
		

        $sPlAnalyticsCode .= '</script>' . "\n";		
        $sPlAnalyticsCode .= '<!-- End Google Analytics -->' . "\n";
        return $sPlAnalyticsCode;
    }
    
}