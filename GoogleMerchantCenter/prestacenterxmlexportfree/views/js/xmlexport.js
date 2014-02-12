var XmlExportModule = {

	/**
	 * @var bool Vychozi nastaveni, jestli se maji checkboxy z vnorenych tabulek propojit do kaskady.
	 */
	useCascade : true,

	/**
	 * @var Array Cache, aby se nemusely pokazde hledat sousedni checkboxy pres jQuery selektory
	 * Pole poli: [ [Element masterCheckbox, Element slave1, Element slave2, ...], ...]
	 */
	cache : [],

	/**
	 * Handler, ktery se povesi na onclick akci vsech checkboxu uvnitr hlavni tabulky
	 * Umoznuje vypnout kaskadovani pro nektere checkboxy (
	 * @param e Event
	 * @return bool
	 */
	checkboxHandler : function(e) {
		var elm = $(e.target), fieldset = [];
		if (!elm.is(':checkbox')) {
			return true;
		}
		/* vychozi nastaveni kaskadovani se da prepsat parametrem u onclick funkce */
		if (e.data !== undefined && e.data.cascade !== undefined) {
			XmlExportModule.useCascade = e.data.cascade;
		}

		if (elm.prop('name') != 'checkme') {
			XmlExportModule.report(elm);
			if (XmlExportModule.useCascade)
				XmlExportModule.cascadeDown(elm);
		} else {
			fieldset = XmlExportModule.getFromCache(elm);
			XmlExportModule.command(fieldset);
			if (XmlExportModule.useCascade)
				XmlExportModule.cascadeUp(fieldset[0]);
		}

		return true;
	},


	/**
	 * Nacte skupinu checkboxu z cache. Pokud tam nejsou, ulozi je.
	 * @return Array
	 */
	getFromCache : function(master) {
		var fieldset = [], i = 0;
		for (i = this.cache.length -1; i >= 0; i--) {
			if (this.cache[i][0].is(master)) {
				return this.cache[i];
			}
		}
		fieldset.push(master);
		var slaves = master.parents('table').first().find('tbody :checkbox');
		for (i = slaves.length -1; i >= 0; i--) {
			fieldset.push($(slaves[i]));
		}
		this.cache.push(fieldset);
		return fieldset;
	},


	/**
	 * Zkusi najít skupiny checkboxu o jednu uroven vys i niz. Pokud najde a stav cilového cbx se lisi,
	 * zmeni jeho stav a pokracuje dal kaskadou.
	 */
	cascadeUp : function(master) {
		/* zavisly checkbox o uroven vys, kteremu patri tato podtabulka */
		var upperSlave = master.parents('tr').prev().first().find(':checkbox');
		/* Tady je nutne porovnavat rovnost, protoze kliknuty prvek jeste zatim nezmenil svuj stav. */
		if (upperSlave.length > 0 && upperSlave.prop('checked') != master.prop('checked')) {
			upperSlave.prop('checked', function(i, val) { return !val; });
			this.report(upperSlave);
		}
	},

	/**
	 * Pokud k zadanemu (zavislemu) checkboxu patri dalsi skupina o uroven niz, zmeni stav jejiho ridiciho prvku.
	 */
	cascadeDown : function(slave) {
		/* ridici checkbox z tabulky o uroven niz, ktera patri tomuto (zavislemu) checkboxu */
		var lowerMaster = slave.parents('tr').next().find(':checkbox[name="checkme"]');
		/* Tady je nutne porovnavat rovnost, protoze kliknuty prvek jeste zatim nezmenil svuj stav. */
		if (lowerMaster.length > 0 && lowerMaster.prop('checked') != slave.prop('checked')) {
			lowerMaster.prop('checked', function(i, val) { return !val; });
			this.command(this.getFromCache(lowerMaster));
		}
	},


	/**
	 * Vsem zavislym checkboxum nastavi hodnotu podle ridiciho.
	 */
	command : function(fieldset) {
		var i;
		/* prvni polozka je master checkbox, vynecha se */
		for (i = fieldset.length -1; i > 0; i--) {
			fieldset[i].prop('checked', fieldset[0].prop('checked'));
			if (this.useCascade)
				this.cascadeDown(fieldset[i]);
		}
	},


	/**
	 * Projde skupinu checkboxu, do ktere patri kliknuty prvek.
	 * Pokud jsou vsechny zavisle zaskrtnute, zaskrtne i ten ridici.
	 */
	report : function(slave) {
		var fieldset = [], allChecked = true, master, i;
		master = slave.parents('table').find(':checkbox[name="checkme"]').first();
		if (master.length == 0) {
			return;
		}
		fieldset = this.getFromCache(master);

		for (i = fieldset.length -1; i > 0; i--) {
			allChecked &= fieldset[i].prop('checked');
		}
		if (allChecked != fieldset[0].prop('checked')) {
			master.prop('checked', allChecked);
			if (this.useCascade)
				this.cascadeUp(master);
		}
	}

}
