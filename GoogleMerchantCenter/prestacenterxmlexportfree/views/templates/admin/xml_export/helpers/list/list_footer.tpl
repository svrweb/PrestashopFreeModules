			</table>
			{if $bulk_actions}
				<p>
					{foreach $bulk_actions as $key => $params}
						<input type="submit" class="button" name="submitBulk{$key}{$table}" value="{$params.text}" {if isset($params.confirm)}onclick="return confirm('{$params.confirm}');"{/if} />
					{/foreach}
				</p>
			{/if}
		</td>
	</tr>
</table>
{if !$simple_header}
	<input type="hidden" name="token" value="{$token}" />
	</form>
{/if}
{* Vlastni handler pro zavisle checkboxy, volitelne s kaskadovanim *}
{if isset($xmlexport.cbx.dependent) && $xmlexport.cbx.dependent}
<script>
{if isset($xmlexport.cbx.cascade)}XmlExportModule.useCascade = {if $xmlexport.cbx.cascade}true{else}false{/if};{/if}
/* zabranuje duplicitnim handlerum */
$('table.table')
  .off('click.xmlexport', ':checkbox', XmlExportModule.checkboxHandler)
  .on('click.xmlexport', ':checkbox', XmlExportModule.checkboxHandler);
</script>
{/if}
{hook h='displayAdminListAfter'}
{if isset($name_controller)}
	{capture name=hookName assign=hookName}display{$name_controller|ucfirst}ListAfter{/capture}
	{hook h=$hookName}
{elseif isset($smarty.get.controller)}
	{capture name=hookName assign=hookName}display{$smarty.get.controller|ucfirst|htmlentities}ListAfter{/capture}
	{hook h=$hookName}
{/if}
{block name="after"}{/block}