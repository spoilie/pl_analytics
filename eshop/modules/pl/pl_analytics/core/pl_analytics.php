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
        $aSavedConfig = oxConfig::getInstance()->getShopConfVar(self::CONFIG_ENTRY_NAME);
        if ($aSavedConfig && count($aSavedConfig) == count($this->_aConfig)) {
            $this->_aConfig = $aSavedConfig;
        }
        else {
            $this->_saveConfig();
        }

    }

    protected function _saveConfig()
    {
        oxConfig::getInstance()->saveShopConfVar( 'arr', self::CONFIG_ENTRY_NAME, $this->_aConfig );
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
        return oxConfig::getInstance()->getActiveView();
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
            $sReturn .="\n_gaq.push([".implode(', ', $aFormed)."]);";
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
        return $this->getConfigValue('ga_domain');
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
     * @param Thankyou $oViewObject
     * @return void
     */
    public function setGaParamsForThankyou($oViewObject)
    {
        $oBasket = $oViewObject->getBasket();
        $oOrder = $oViewObject->getOrder();
        $dTaxAmount = $oOrder->oxorder__oxartvatprice1->value + $oOrder->oxorder__oxartvatprice2->value;
        $dShippingTotal =
                  $oOrder->getOrderDeliveryPrice()->getBruttoPrice()
                + $oOrder->getOrderPaymentPrice() ->getBruttoPrice()
                + $oOrder->getOrderWrappingPrice()->getBruttoPrice()
        ;
// TODO: Get State
/*
        if ( $oOrder->oxorder__oxbillstateid->value && $oOrder->oxorder__oxbillstateid->value != -1 ) {
          $oState = oxNew( 'oxstates' );
          $oState->load( $oOrder->oxorder__oxbillcountryid->value );
          $sState = $oState->oxcountry__oxtitle;
        }         
*/
        // Get Country
        if ( $oOrder->oxorder__oxbillcountryid->value && $oOrder->oxorder__oxbillcountryid->value != -1 ) {
          $oCountry = oxNew( 'oxcountry' );
          $oCountry->load( $oOrder->oxorder__oxbillcountryid->value );
          $sCountry = $oCountry->oxcountry__oxtitle;
        }         
        
        $this->addPushParams(
            '_addTrans',
            $oOrder->oxorder__oxordernr->value,
            $this->getGaDomain(),
            $oOrder->oxorder__oxtotalnetsum->value,
            number_format($dTaxAmount,2),
            number_format($dShippingTotal,2),
            $oOrder->oxorder__oxbillcity->value,
            '',
            (string)$sCountry
        );
        $this->_setEcommerceItemsByBasket($oBasket,$oOrder->oxorder__oxordernr->value);
        $this->addPushParams('_trackTrans');
        
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
        $sPlAnalyticsCode .= '<script type="text/javascript">';
        $sPlAnalyticsCode .= 'var _gaq = _gaq || [];';
        $this->addPushParams('_setAccount', $this->getGaUaId());
        $this->addPushParams('_gat._anonymizeIp');
        $this->addPushParams('_trackPageview');

        $this->_setGaParamsByViewObject();

        $sPlAnalyticsCode .= $this->generateParams();
        $sPlAnalyticsCode .= "
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })(); ";

        $sPlAnalyticsCode .= '</script>';
        $sPlAnalyticsCode .= '<!-- End Google Analytics -->';
        return $sPlAnalyticsCode;
    }
    
}