<?php

defined('_JEXEC') or die();

jimport('joomla.plugin.plugin');

class PlgSystemCatsignal extends JPlugin {

	/**
	 * Method to catch the onAfterRender event.
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function onAfterRender() {
		// Use this plugin only in site application, and check if we should insert data into the html header
		if (JFactory::getApplication()->isAdmin() || JFactory::getDocument()->getType() !== 'html' || JFactory::getApplication()->input->get('tmpl', '', 'cmd') === 'component') {
			return true;
		}

		// Set the variables
		$campaign = $this->params->get('campaign', 'all');
		$expiration = $this->params->get('expiration', null);
		$variant = $this->params->get('variant', 'banner');

		// EJECT
		if (!empty($expiration) && strtotime('now') >= strtotime($expiration))
			return true;

		// Check if the campaign should be included
		if (!empty($campaign) && strtolower($campaign) !== 'all') {
			$campaignCode = "_idl.campaign = \"{$campaign}\";";
		}

		// Prepare the JavaScript
		$javascript = <<<CATSIGNALJAVASCRIPT
<script type="text/javascript">
    window._idl = {};
    _idl.variant = "$variant";
    $campaignCode
    (function() {
        var idl = document.createElement('script');
        idl.type = 'text/javascript';
        idl.async = true;
        idl.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'members.internetdefenseleague.org/include/?url=' + (_idl.url || '') + '&campaign=' + (_idl.campaign || '') + '&variant=' + (_idl.variant || 'banner');
        document.getElementsByTagName('body')[0].appendChild(idl);
    })();
</script>

CATSIGNALJAVASCRIPT;

		// Adjust the buffer.
		$buffer = JResponse::getBody();
		$pos = strrpos($buffer, "</body>");
		// todo: FUTURE FEATURE: Double-check that there isn't any duplicate code.
		$buffer = substr($buffer, 0, $pos) . $javascript . substr($buffer, $pos);
		JResponse::setBody($buffer);

		return true;
	}
}
