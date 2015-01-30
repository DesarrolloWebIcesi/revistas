{**
 * templates/common/footer.tpl
 *
 * Copyright (c) 2013-2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site footer.
 *
 *}
{if $displayCreativeCommons}
	{translate key="common.ccLicense"}
{/if}
{if $pageFooter}
	<br /><br />
	<div id="pageFooter">{$pageFooter}</div>
{/if}
{call_hook name="Templates::Common::Footer::PageFooter"}
</div><!-- content -->
</div><!-- main -->
</div><!-- body -->

{get_debug_info}
{if $enableDebugStats}{include file=$pqpTemplate}{/if}
<!-- 2014-09-26 damanzano custom footer-->
<div id="footer_bottom">
	<a title="Universidad Icesi" target="_blank" href="http://www.icesi.edu.co">Universidad Icesi</a> Calle 18 No. 122 - 135 Pance - Santiago de Cali | PBX 57(2) 555 2334 Fax 555 1441 <br> Copyright &copy; {$smarty.now|date_format:"%Y"} <a title="Universidad Icesi" href="http://www.icesi.edu.co/">www.icesi.edu.co </a>- <a title="Pol&iacute;tica de privacidad" href="http://www.icesi.edu.co/politica_privacidad.php">Pol&iacute;tica de privacidad</a>
</div>
</div><!-- container -->
</body>
</html>
