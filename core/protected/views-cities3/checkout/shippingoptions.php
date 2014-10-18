<?php
	$form = $this->beginWidget(
		'CActiveForm',
		array(
			'htmlOptions' => array(
				'class' => "section-content",
				'id' => "shipping",
				'novalidate' => '1'
			)
		)
	);
?>
<nav class="steps">
	<ol>
		<li class="current"><span class="webstore-label"></span><?php echo Yii::t('checkout', 'Shipping')?></li>
		<li class=""><span class="webstore-label"></span><?php echo Yii::t('checkout', 'Payment')?></li>
		<li class=""><span class="webstore-label"></span><?php echo Yii::t('checkout', 'Confirmation')?></li>
	</ol>
</nav>
<h1><?php echo Yii::t('checkout','Shipping'); ?></h1>
<p class="introduction">
	<?php
		echo Yii::t('checkout', "Confirm your shipping address and choose your preferred shipping method.");
	?>
</p>
<div class="address-block address-block-alter">
	<p class="webstore-label">
		<?php
		echo $model->shippingFirstName . ' ' . $model->shippingLastName . '<br>' . $model->getHtmlShippingAddress();
		?>
	</p>
	<button type="button" class="small" onclick="window.location='<?= Yii::app()->user->IsGuest ? '/checkout/shipping/' : '/checkout/shippingaddress' ?>'">
		<?php
			echo CHtml::link(Yii::t('cart','Change Address'), Yii::app()->user->IsGuest ? '/checkout/shipping/' : '/checkout/shippingaddress');
		?>
	</button>
</div>
<h3><?php echo Yii::t('checkout','Shipping Options'); ?></h3>
<div class="error-holder">
	<?php echo $error; ?>
</div>
<?php
	// The values of these hidden fields are set by the JavaScript when a
	// shipping option is selected.
	echo $form->hiddenField(
		$model,
		'shippingProvider',
		array('class' => 'shipping-provider-id')
	);

	echo $form->hiddenField(
		$model,
		'shippingPriority',
		array('class' => 'shipping-priority-label')
	);
?>

<table class="shipping-options">
	<thead>
		<tr>
			<th>
				<?php echo Yii::t('checkout', 'Shipping Method'); ?>
			</th>
		</tr>
	</thead>
	<?php
		$arrShippingOption = CheckoutController::formatCartScenarios($arrCartScenario);

		$orderSummaryOptions = array(
			'class' => '.summary',
			'rates' => $arrShippingOption
		);

		Yii::app()->clientScript->registerScript(
			'instantiate OrderSummary',
			'$(document).ready(function () {
				orderSummary = new OrderSummary(' . CJSON::encode($orderSummaryOptions) . ');
			});',
			CClientScript::POS_HEAD
		);

		foreach ($arrShippingOption as $optionIdx => $shippingOption): ?>
		<tr>
			<td>
				<?php
					$id = CHtml::activeId('MultiCheckoutForm', 'shippingProvider_' . $optionIdx);
					$isChecked = $shippingOption['providerId'] == $model->shippingProvider &&
						$shippingOption['priorityLabel'] == $model->shippingPriority;

					echo CHtml::radioButton(
						'shippingOption',
						$isChecked,
						array(
							'id' => $id,
							'data-provider-id' => $shippingOption['providerId'],
							'data-priority-label' => $shippingOption['priorityLabel'],
							'onclick' => 'orderSummary.optionSelected(this);'
						)
					);

					echo CHtml::label(
						$shippingOption['shippingOptionPriceLabel'],
						$id
					);
				?>
			</tr>
		</td>
	<?php endforeach; ?>
</table>
<footer class="submit submit-small">
	<?php echo
			CHtml::submitButton(
				'Submit',
				array(
					'type' => 'submit',
					'class' => 'button',
					'value' => Yii::t('checkout', "Proceed to Payment")
				)
			);
	?>
</footer>

<?php $this->endWidget(); ?>

<aside class="section-sidebar webstore-sidebar-summary">
	<?php $this->renderPartial('_ordersummary'); ?>
</aside>
