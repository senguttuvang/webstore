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
   
 * @copyright  Copyright (c) 2011 Xsilva Systems, Inc. http://www.lightspeedretail.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 
 */

/**
 * Shipping address block for new registration
 *
 * 
 *
 */

?>
	<fieldset>
		<legend><span class="red">*</span> <?php _xt("Shipping Address") ?></legend>





<?php  if(isset($this->lstCRShipPrevious) && $this->lstCRShipPrevious->Visible):   ?>

		<div>
			<dl>
				<dt><label for="Previously shipped to"><?php _xt("Previous shipped address") ?></label></dt>
				<dd><?php $this->lstCRShipPrevious->Render(); ?></dd>
			</dl>
		</div>
<?php  endif; ?>

<?php  if(isset($this->blnShowShippingNames)):   ?>
		<div>
			<dl>
				<dt><label for="First Name"><?php _xt("First Name") ?></label></dt>
				<dd><?php $this->txtCRShipFirstname->RenderWithError(); ?></dd>
			</dl>
		</div>

		<div>
			<dl>
				<dt><label for="Last Name"><?php _xt("Last Name") ?></label></dt>
				<dd><?php $this->txtCRShipLastname->RenderWithError(); ?></dd>
			</dl>
		</div>
<?php  endif; ?>

<?php  if(isset($this->txtCRShipPhone)):   ?>
		<div>
			<dl>
				<dt><label for="Phone"><?php _xt("Phone") ?></label></dt>
				<dd><?php $this->txtCRShipPhone->RenderWithError(); ?></dd>
			</dl><br />
		</div>
<?php  endif; ?>

<?php  if(isset($this->txtCRShipCompany)):   ?>
		<div>
			<dl>
				<dt><label for="Company"><?php _xt("Company") ?></label></dt>
				<dd><?php $this->txtCRShipCompany->RenderWithError(); ?></dd>
			</dl><br />
		</div>
<?php  endif; ?>

		

		<div>
		<dl>
			<dt><label for="Address"><?php _xt("Address") ?></label></dt>
		
			<dd><?php $this->txtCRShipAddr1->RenderWithError() ?></dd>
			
			<dd><?php $this->txtCRShipAddr2->RenderWithError() ?></dd>
		</dl>
		
		
		</div>
		
		<div>
		<dl>
			<dt><label for="City" class="city2"><?php _xt("City") ?></label></dt>
		
			<dd><?php $this->txtCRShipCity->RenderWithError() ?></dd>
		</dl>
		</div>
		
		<div>
		<dl>
			<dt><label for="Country"><?php _xt("Country") ?></label></dt>
		
			<dd><?php $this->txtCRShipCountry->RenderWithError() ?></dd>
		</dl>
		</div><br />
		
		<div>
		<dl>
			<dt><label for="State"><?php _xt("State/Province") ?></label></dt>
		
			<dd><?php $this->txtCRShipState->RenderWithError() ?></dd>
		</dl>
		</div><br />
		
		<div>
		<dl>
			<dt><label for="Zip" class="zip"><?php _xt("Zip/Postal Code") ?></label></dt>
		
			<dd><?php $this->txtCRShipZip->RenderWithError() ?></dd>
		</dl>
		</div>

</fieldset>


