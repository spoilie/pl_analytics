[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]
[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
[{else}]
    [{assign var="readonly" value=""}]
[{/if}]
<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="cl" value="pl_analytics_setup">
    <input type="hidden" name="language" value="[{$actlang}]">
</form>
<form name="myedit" id="myedit" action="[{$oViewConf->getSelfLink()}]" method="post">
	[{$oViewConf->getHiddenSid()}]
	<input type="hidden" name="cl" value="pl_analytics_setup">
	<input type="hidden" name="fnc" value="">
	<input type="hidden" name="language" value="[{$actlang}]">
	<table cellspacing="0" cellpadding="0" border="0" width="98%">
		<tr>
			<td valign="top" class="edittext">
				<table cellspacing="0" cellpadding="0" border="0">		
					[{foreach from=$oView->getConfigValues() item='aConfigValueOptions' key='sConfigKey'}]
					<tr>
						<td class="edittext">
							[{oxmultilang ident="PL_ANALYTICS_CONFIG_"|cat:$sConfigKey}]
                             
						</td>
						<td class="edittext">
							[{if $aConfigValueOptions.input_type == 'text'}]
                        
							<input type="text" class="editinput" size="40" maxlength="255"
                               
								   name="editval[[{$sConfigKey}]]" value="[{$aConfigValueOptions.value}]" [{$readonly}]>
							[{/if}]
							[{if $aConfigValueOptions.input_type == 'select'}]
                             
							<select name="editval[[{$sConfigKey}]]">
                            
								[{foreach from=$aConfigValueOptions.options item='sConfigOption'}]
									<option value="[{$sConfigOption}]"[{if $aConfigValueOptions.value ==$sConfigOption}] selected="selected"[{/if}]>[{$sConfigOption}]</option>  
								[{/foreach}]
							</select>
                            
							[{/if}]
							[{oxinputhelp ident="HELP_PL_ANALYTICS_CONFIG_"|cat:$sConfigKey}]
						</td>
					</tr>
					[{/foreach}]
					<tr>
						<td class="edittext"><br><br>
							[{assign var="oPlAnalytics" value=$oView->getPlAnalytics()}]
							<div>Version: [{$oPlAnalytics->getVersion()}]</div>
						</td>
						<td valign="top" class="edittext"><br><br>
							<input type="submit" class="edittext" id="oLockButton"
								   value="[{oxmultilang ident="GENERAL_SAVE"}]"
								   onclick="Javascript:document.myedit.fnc.value='save' [{$readonly}]><br>
						</td>
                        
					</tr>
				</table>	
			</td>
			
		</tr>
	</table>
</form>

<table><tr><td valign="top">
<div>
	<strong>Beim Entwickler für die Anpassung des Scriptes bedanken</strong><br />
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="LGWZV8TWKH3AJ"><input type="image" src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="Jetzt einfach, schnell und sicher online bezahlen – mit PayPal."><img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1"></form>
</div></td></tr></table>
			
		

[{include file="bottomnaviitem.tpl"}]
[{include file="bottomitem.tpl"}]