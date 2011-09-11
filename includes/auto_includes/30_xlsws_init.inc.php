<?php
/*
  LightSpeed Web Store
 
  NOTICE OF LICENSE
 
  This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@lightspeedretail.com <mailto:support@lightspeedretail.com>
 * so we can send you a copy immediately.
 
  DISCLAIMER
 
 * Do not edit or add to this file if you wish to upgrade Web Store to newer
 * versions in the future. If you wish to customize Web Store for your
 * needs please refer to http://www.lightspeedretail.com for more information.
 
 * @copyright  Copyright (c) 2011 Xsilva Systems, Inc. http://www.lightspeedretail.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 
 */

if (!empty(QApplication::$Database)) {
	if (!defined('__PREPEND_QUICKINIT__')) {
		global $XLSWS_VARS;
		$XLSWS_VARS = array_merge($_GET, $_POST);

		global $customer;
		if (isset($_SESSION['customer']))
			$customer = $_SESSION['customer'];

		QApplication::$EnableSession = true;
		QApplication::InitializeSession();

		// Define the Form State handler to save form states
		QForm::$FormStateHandler = 'QFormStateHandler';
        QForm::$EncryptionKey = null;

        // Define amount of pages to view in pagination
        XLSPaginator::$intIndexCount = 7;
	}

	QApplication::$EncodingType = _xls_get_conf('ENCODING' , "UTF-8");

	QApplication::InitializeI18N();
	QApplication::InitializeTimezone();
	QApplication::InitializeLocale();
}
