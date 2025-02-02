// Redirection
$(document).ready(function () {
    $('#kz_minMenu select').on('change', function () {
        window.location = $(this).find('option:selected').val();
    });

   // ETABLISSEMENT Actions sur "Vos Diplômés" et "Vos Partenaires
// -------------------------------------------------------------
// Met à jour le checkSchool dans "Vos Diplômés" lorsque l'on est connecté en tant qu'établissement
   $("#persondegree, #kz_table").on('click', '.validStudent',  function () {
      let idPersonDegree = $(this).attr("id").replace("validStudent", "");
      validPersonDegreeBySchool("validStudent", idPersonDegree, $(this).val());
   });

// Initialisation du checkbox de la liste de toutes les entreprises
   $(".updateCompanySchool").each(function () {
      let idNumCompany = $(this).attr("id").replace("companySchool", "");
      let idCompany = '#' + $(this).attr("id");
      $('#selectedCompanies option').each(function () {
         if (idNumCompany == $(this).val()) {
            $(idCompany).prop('checked', true);
         }
      });
   });
// Met à jour "Vos Partenaires" lorsque l'on est connecté en tant qu'établissement
   $(".updateCompanySchool").on('click', function () {
      let idCompany = $(this).attr("id").replace("companySchool", "");
      updateCompanySchool(".updateCompanySchool", idCompany);
   });
// -------------------------------------------------------------

   $('#actions form').css('display', 'inline-block');

})

// met l'attribut selected sur option selectionné
global.makeSelected = function makeSelected(name) {
    $('#kz_minMenu option[name="' + name + '"]').attr('selected', 'selected')
}

//affiche / cache les div concernées si checkbox sur false
global.showHiddenDivByCheckBox = function showHiddenDivByCheckBox(idCheckBox, classDiv) {
    if ($('#idCheckBox').is('checked')) {
        getTranslation().then(function (translations) {
            alert(translations["js.checkbox_is_checked"]);
        });
    } else {
        getTranslation().then(function (translations) {
            alert(translations["js.checkbox_is_not_checked"]);
        });
    }
}

// Permet de supprimer un element
global.deleteElement = function deleteElement(route) {
    getTranslation().then(function (translations) {
        if (confirm(translations['js.sure_to_delete_item']))
            window.location.href = route;
    });
}

/**
 *
 * @param callback
 * @returns {*|jQuery}
 */
global.datatable = function datatable(retrieve = false) {
    let splitPath = window.location.toString().split('/' + getCurrentLocale() + '/');
    let jsonLangage = splitPath[0] + '/locale/' + getCurrentLocale().toLowerCase() + '_' + getCurrentLocale().toUpperCase() + '.json'

    let options = {
        language: {
            url: jsonLangage
        },
        initComplete: function () {
            $('#kz_table_wrapper input').addClass('form-control form-control-sm ')
            $('#kz_table_wrapper select').addClass('form-control form-control-sm ')
            $('#kz_table_wrapper .row').css('width', '100%')
            $('#kz_table').parent().css('width', '100%')
        },
        dom: 'Bfrtip',
        buttons: [
            'csv', 'excel', 'pdf', 'print'
        ]
    };
    if (retrieve == true) {
        options = {retrieve: true}
    }
    return $('#kz_table').DataTable(options);
}

// Datepicker
global.datepicker = function datepicker() {
    var today = new Date();
    today.setFullYear(today.getFullYear() - 12);

    $('.birthdate').datepicker({
        language: 'fr',
        startDate: new Date(today.getFullYear() - 100, today.getMonth(), today.getDate()), // 100 ans en arrière
        endDate: today,
    });

    $('.datepicker').datepicker({
        language: 'fr',
    });

    $('.datepicker').css('padding', '9px');
}

datepicker();
$('div.tabs').each(function () {
    // For each set of tabs, we want to keep track of
    // which tab is active and its associated content
    var $active, $content, $links = $(this).find('a');

    // If the location.hash matches one of the links, use that as the active tab.
    // If no match is found, use the first link as the initial active tab.
    $active = $($links.filter('[href="' + location.hash + '"]')[0] || $links[0]);
    $active.addClass('active');

    $content = $($active[0].hash);

    // Hide the remaining content
    $links.not($active).each(function () {
        $(this.hash).hide();
    });

    // Bind the click event handler
    $(this).on('click', 'a', function (e) {
        // Make the old tab inactive.
        $active.removeClass('active');
        $content.hide();

        // Update the variables with the new link and content
        $active = $(this);
        $content = $(this.hash);

        // Make the tab active.
        $active.addClass('active');
        $content.show();

        // Prevent the anchor's default click action
        e.preventDefault();
    });
});

// Changement de langue
$('#locale').on('click', function () {
    let url = window.location.toString();
    let regex = new RegExp('/fr/')
    if (regex.test(url)) {
        url = url.replace('/fr/', '/en/');
        console.log('francais')
    } else {
        url = url.replace('/en/', '/fr/');
        console.log('anglais')
    }

    window.location.href = url;
    // alert(url);
})

/*-----------------------------------*\
   Fonctions communes aux formulaires
\*-----------------------------------*/

/**
 * Permet d'ajouter cacher/montrer les div avec les CheckBox (glissieres OUI/NON)
 * @param idSelect
 * @param classOther
 * @param mode
 */
global.hideBlockFlexListener = function hideBlockFlexListener(idPrefix, select, classOther, mode = 'oui', selectRequiredOffs, inputRequiredOffs) {
    let idSelect = idPrefix + select;
    let classHideFlex = '.hide_flex_' + classOther;
    let classHideBlock = '.hide_block_' + classOther;

    console.log("idSelect=" + idSelect);

    function hideBlockFlex() {
        let valCheckbox = "non";
        if ($(idSelect).is(":checked"))
            valCheckbox = "oui";
        console.log("hideBlockFlexListener --> checkbox=" + valCheckbox + " | mode=" + mode);

        if (valCheckbox == mode) {
            $(classHideFlex).removeAttr('hidden');
            $(classHideBlock).removeAttr('hidden');

            // on remet l'attribut required sur les champs selects et inputs affichés
            if (selectRequiredOffs) {
                selectRequiredOffs.forEach(function (requiredOff) {
                    let idRequiredOff = idPrefix + requiredOff;
                    // console.log("ON -->  " + idRequiredOff);
                    $(idRequiredOff).attr('required', 'required');
                });
            }
        } else {
            $(classHideFlex).attr('hidden', 'hidden');
            $(classHideBlock).attr('hidden', 'hidden');

            // on enleve l'attribut required sur les champs selects et inputs cachés
            if (selectRequiredOffs) {
                selectRequiredOffs.forEach(function (requiredOff) {
                    let idRequiredOff = idPrefix + requiredOff;
                    // console.log("OFF -->  " + idRequiredOff);
                    $(idRequiredOff).removeAttr('required');
                });
            }
            if (inputRequiredOffs) {
                inputRequiredOffs.forEach(function (requiredOff) {
                    let idRequiredOff = idPrefix + requiredOff;
                    // console.log("OFF -->  " + idRequiredOff);
                    $(idRequiredOff).removeAttr('required');
                });
            }
        }
    }

    // initialisation au chargement de la page
    hideBlockFlex();

    // Event
    $(idSelect).on('change', function () {
        hideBlockFlex();
    });
}


/**
 * Permet d'initialiser les div avec les CheckBox (glissieres OUI/NON)
 * @param idSelect
 * @param classOther
 * @param mode
 */
global.clearBlockFlex = function clearBlockFlex(idPrefix, checkbox, mode, selectRequiredOffs, inputRequiredOffs) {
    let idCheckbox = idPrefix + checkbox;
    let valCheckbox = "non";
    if ($(idCheckbox).is(":checked"))
        valCheckbox = "oui";
    // console.log("clearBlockFlex --> checkbox=" + valCheckbox + " | mode=" + mode);

    if (valCheckbox == mode) {
        // on initialise les selects
        if (selectRequiredOffs) {
            selectRequiredOffs.forEach(function (requiredOff) {
                let idRequiredOff = idPrefix + requiredOff;
                $(idRequiredOff).children('option').remove();

            });
        }
        // on initialise les inputs
        if (inputRequiredOffs) {
            inputRequiredOffs.forEach(function (requiredOff) {
                let idRequiredOff = idPrefix + requiredOff;
                $(idRequiredOff).val("");
            });
        }
    }
}

/**
 * Initialisation des zones masquées et ajout de l'option Autre
 * @param idSelect
 * @param idOther
 * @param classOther
 * @param optionAutre
 */
global.initChampsAutre = function initChampsAutre(idPrefix, select, other, classHideOther, optionAutre) {
    //console.log("1 |" + idPrefix + "|" +  select + "|" +  other + "|" +  classHideOther + "|" +  optionAutre + "|")
    let idSelect = idPrefix + select;
    let idOther = idPrefix + other;

    // Remet l'option Autre si inexistante apres action sur un valider par exemple
    $(idSelect).on('click', function () {
        // if ($(idSelect).text() && $(idSelect).text().indexOf("Autre")==-1) {
        if ($(idSelect).text() && $(idSelect).text().indexOf(translations["js.other"]) == -1) {
            $(idSelect).append(new Option(optionAutre, ''));
        }
    });
    $(idOther).on('click', function () {
        // if ($(idSelect).text() && $(idSelect).text().indexOf("Autre")==-1) {
        if ($(idSelect).text() && $(idSelect).text().indexOf(translations["js.other"]) == -1) {
            $(idSelect).append(new Option(optionAutre, ''));
            // $(idSelect + " option[value= '']").attr('selected', 'selected');
        }
    });

    $(idOther).on('change', function () {
        if ($(idOther).val()) {
            if ($(idSelect).prop(!"multiple")) {
                $(idSelect).removeAttr('required');
            }
        } else {
            $(idSelect).prop("required", true);
        }
    });

    // Ajout de Autre  à la création ou édition du formulaire
    // console.log("detect Autre=" + optionAutre);
    if (optionAutre && optionAutre.indexOf(translations["js.other"]) != -1) {
        if ($(idSelect).text() && $(idSelect).text().indexOf(translations["js.other"]) == -1) {
            $(idSelect).append(new Option(optionAutre, ''));
        }
    }

    // Si champ Autre est renseigné
    // console.log("1|" +idOther + "|");
    if (!$(idOther).val()) {
        $(classHideOther).attr('hidden', 'hidden');
        $(idOther).removeAttr('required');
        // } else if ($(idOther).val().length > 0) {
    } else {
        // console.log("detection other renseigné " + idOther);
        $(classHideOther).removeAttr('hidden')
        $(idSelect + " option[value='']").attr('selected', 'selected');
        if (!$(idSelect).prop(!"multiple")) {
            $(idSelect).removeAttr('required');
        }
        $(idOther).attr('required', 'required');
    }

    // Si option Autre est selectionnée
    // if($(idSelect + ' option:selected').text().indexOf("Autre")==-1) {
    if ($(idSelect + ' option:selected').text().indexOf(translations["js.other"]) == -1) {
        // if($(idSelect + ' option:selected').val().indexOf("")==-1) {
        //console.log("option Autre n'est pas selectionnée : " + idSelect + " ;")
        $(classHideOther).attr('hidden', 'hidden');
    } else {
        //console.log("option Autre est selectionnée: " + idSelect + " ;")
        $(classHideOther).removeAttr('hidden');
    }
}

/**
 * Masquage, affichage des chapitres Autres en fonction du select contenant Autre
 * @param idSelect
 * @param idOther
 * @param classOther
 */
global.masquageChampsAutre = function masquageChampsAutre(idPrefix, select, other, classHideOther) {
    let idSelect = idPrefix + select;
    let idOther = idPrefix + other;

    // Event sur le input other
    $(idOther).on('change', function () {
        if ($(idOther).val()) {
            $(idOther).attr('required', 'required');
            $(idSelect).removeAttr('required');
        }
    });

    // Event sur le select
    $(idSelect).on('change', function () {
        // if ($(idSelect + " option:selected").text().indexOf('Autre')!=-1) {
        if ($(idSelect + " option:selected").text().indexOf(translations['js.other']) != -1) {
            $(classHideOther).removeAttr('hidden');
            $(idSelect).removeAttr('required');
            $(idOther).attr('required', 'required');
        } else {
            // On vide le change autre puis on le cache
            $(idOther).val("");
            $(classHideOther).attr('hidden', 'hidden');
            $(idOther).removeAttr('required');
            if ($(idSelect).text())
                $(idSelect).attr('required', 'required');
        }
        if ($(idOther).text() == "") {
            if ($(idSelect).text())
                $(idSelect).attr('required', 'required');
        }
    });
}


/*
 * Remplace les accents
 * @param str
 * @returns {string}
 */
global.replaceAccents = function replaceAccents(str) {
    const ACCENTS = 'ÀÁÂÃÄÅàáâãäåÒÓÔÕÕÖØòóôõöøÈÉÊËèéêëðÇçÐÌÍÎÏìíîïÙÚÛÜùúûüÑñŠšŸÿýŽž';
    const NON_ACCENTS = "AAAAAAaaaaaaOOOOOOOooooooEEEEeeeeeCcDIIIIiiiiUUUUuuuuNnSsYyyZz";

    const strAccents = str.split('');
    const strAccentsOut = new Array();

    const strAccentsLen = strAccents.length;

    for (let y = 0; y < strAccentsLen; y++) {
        if (ACCENTS.indexOf(strAccents[y]) != -1) {
            strAccentsOut[y] = NON_ACCENTS.substr(ACCENTS.indexOf(strAccents[y]), 1);
        } else {
            strAccentsOut[y] = strAccents[y];
        }
    }

    const newString = strAccentsOut.join('');
    return newString;
}

/**
 * Calcul et mise à jour des Lat et lng avec Geocoder de Maps
 * @param address
 * @param latInputID
 * @param lngInputID
 */
global.geocodeAddressLocation = async function geocodeAddressLocation(address, latInputID, lngInputID) {
    let geocoder = new google.maps.Geocoder;
    let erreur = true;
    await geocoder.geocode({'address': address}, function (results, status) {
        // console.log("Result: " + results + ", Status: " + status)
        if (status == 'OK') {
            let lat = results[0].geometry.location.lat();
            let lng = results[0].geometry.location.lng();
            $(latInputID).val(lat.toString());
            $(lngInputID).val(lng.toString());
            console.log(address + " coo->" + lat + "," + lng);
            erreur = false;
        } else {
            console.log("Echec de recherche Latitude/Longitude : " + address);
        }
    });
    return erreur;
}

/**
 *
 * @param number
 * @param road
 * @param locality
 * @param city
 * @param region
 * @param country
 * @returns {string}
 */
global.createMapsAddress = function createMapsAddress(number, road, locality, city, region, country) {

    // console.log("test1 " , number, road, locality, city, region, country )
    let address = "";
    if (number == "undefined") {
        number = ''
    }
    ;
    if (road == "undefined") {
        road = ''
    }
    ;
    if (locality == "undefined") {
        locality = ''
    }
    ;
    if (city == "undefined") {
        city = ''
    }
    ;
    if (region == "undefined") {
        region = ''
    }
    ;
    if (country == "undefined") {
        country = ''
    }
    ;

    if (number) {
        if (!number.toLowerCase().replaceAll('é', 'e').startsWith('select'))
            address += number + ",";
    }
    if (road) {
        if (!road.toLowerCase().replaceAll('é', 'e').startsWith('select'))
            address += road + ",";
    }
    if (locality) {
        if (!locality.toLowerCase().replaceAll('é', 'e').startsWith('select'))
            address += locality + ",";
    }

    if ((city) && (!address.includes(city))) {
        if (!city.toLowerCase().replaceAll('é', 'e').startsWith('select'))
            address += city + ",";
    }
    if ((region) && (!address.includes(region))) {
        if (!region.toLowerCase().replaceAll('é', 'e').startsWith('select'))
            address += region + ",";
    }
    if ((country) && (!address.includes(country))) {
        if (!country.toLowerCase().replaceAll('é', 'e').startsWith('select'))
            address += country + ",";
    }
    // console.log("test2 ->" , address)
    return address;
}

/**
 *
 * @param allActivites
 * @param idSectorArea
 * @param idActivites
 */
global.initActivities = function initActivities(allActivites, idPrefix, sectorAreaName, activityName, multiple = false, callback) {
// function initActivities(allActivites, idPrefix, sectorAreaName, activityName, callback) {
    let idSectorArea = idPrefix + sectorAreaName;
    let idActivities = idPrefix + activityName;
    let optionSelected = [];

    // On met les activities selectionnées dans un tableau
    if (multiple) {
        $(idActivities + ' option').each(function () {
            optionSelected.push($(this).val());
        });
    } else {
        optionSelected.push($(idActivities + ' option:selected').val());
    }

    let idValueSectorArea = $(idSectorArea + ' option:selected').val();

    // on réinitialise les activités
    $(idActivities).children('option').remove();

    // on remet le placeOlder pour les selects uniques si secteur d'activité absent
    // console.log("sectorAreaName=" + idValueSectorArea);
    if (multiple == false) {
        // if(! $(idActivities).text()) {
        if (!idValueSectorArea) {
            $(idActivities).append($('<option value=-1/>').text(translations['js.select_sector']).prop('selected', false));
        } else {
            $(idActivities).append($('<option disabled value=-1/>').text(translations['js.select_activity']).prop('selected', false));
        }
        // }
    }

    // On ajoute toutes les activités correspondantes au sectorArea
    $.each(allActivites, function () {
        if (this.sectorArea == idValueSectorArea) {
            let activity = this.name;
            if (translations[this.name])
                activity = translations[this.name]
            $(idActivities).append(new Option(activity, this.id));
        }
    })

    // on remet les selected sur les activités
    // for(var i in optionSelected) {
    //    console.log("  --->  " + optionSelected[i]);
    // }
    if (optionSelected) {
        $(idActivities + " option").each(function () {
            if ($.inArray($(this).val(), optionSelected) !== -1) {
                $(this).prop('selected', true);
            }
        });
    }

    if (typeof callback === "function") callback();
}

/**
 * Met la liste des activites dans un tableau
 * @param idAllActivities
 */
global.getAllActivities = function getAllActivities(idAllActivities) {
    let allActivities = [];
    // On met les activities selectionnées dans un tableau
    $('#allActivities option').each(function () {
        allActivities.push({
            id: $(this).val(),
            name: $(this).attr('name'),
            sectorArea: $(this).text()
        })
    });

    return allActivities;
}
/**
 * Permet l'écoute au changement des metiers en fonction des secteurs d'activités
 * @param idSectorArea
 * @param sectorAreaName
 * @param activityName
 * @param callback
 */
global.listenChangeSectorArea = function listenChangeSectorArea(allactivities, idPrefix, sectorAreaName, activityName, otherActivity, classOtherHidden, multiple = false) {
    let idSectorArea = idPrefix + sectorAreaName;
    let idActivities = idPrefix + activityName;

    $(document).on('change', idSectorArea, function () {
        $(idActivities + ' option').remove()      // Vider toutes activités
        let idValueSectorArea = $(idSectorArea + ' option:selected').val();  // Recupérer l'id du sectorArea selectionné

        // on rajoute les activités
        $.each(allactivities, function () {
            if (this.sectorArea == idValueSectorArea) {
                // $(idActivities).append(new Option(this.name, this.id)); // Ajout activité
                let activity = this.name;
                if (translations[this.name])
                    activity = translations[this.name]
                $(idActivities).append(new Option(activity, this.id)); // Ajout activité
            }
        })

        //on remet le placeOlder
        if (multiple == false) {
            $(idActivities).prepend("<option value='-1'>" + translations['js.select_activity'] + "</option>");
            $(idActivities + " option[value='-1']").prop("selected", true);
        }

        // initChampsAutre(idPrefix, activityName, otherActivity, classOtherHidden, "Autre métier");
        initChampsAutre(idPrefix, activityName, otherActivity, classOtherHidden, translations["js.other_job"]);
        masquageChampsAutre(idPrefix, activityName, otherActivity, classOtherHidden, multiple);
    })
}

/**
 * Permet de changer les metiers en fonction des secteurs d'activités
 * @param idSectorArea
 * @param sectorAreaName
 * @param activityName
 * @param callback
 */
global.changeSectorArea = function changeSectorArea(idSectorArea, sectorAreaName, activityName, callback) {
    let $field = $(idSectorArea)
    let $sectorAreaField = $(idSectorArea)
    let $form = $field.closest('form')

    let target = '#' + $field.attr('id').replace(sectorAreaName, activityName)

    // Les données à envoyer en Ajax
    let data = {}
    data[$sectorAreaField.attr('name')] = $sectorAreaField.val()

    // On soumet les données
    $.post($form.attr('action'), data).then(function (data) {
        // On récupère le nouvmeeau <select>
        let $input = $(data).find(target)
        // On remplace notre <select> actuel
        $(target).replaceWith($input)
        if (typeof callback === "function") callback();
    })
}


/**
 * Permer de changer les regions, villes en fonction des pays
 * @param idCountry
 * @param idRegion
 * @param countryName
 * @param regionName
 * @param cityName
 * @param callback
 */
global.listenChangeCountryRegion = function listenChangeCountryRegion(idCountry, idRegion, countryName, regionName, cityName, callback) {
    $(document).on('change', idCountry + ', ' + idRegion, function () {
        let $field = $(this)
        let $regionField = $(idCountry)
        let $form = $field.closest('form')
        let target = '#' + $field.attr('id').replace(regionName, cityName).replace(countryName, regionName)
        // Les données à envoyer en Ajax
        let data = {}
        data[$regionField.attr('name')] = $regionField.val()
        data[$field.attr('name')] = $field.val()
        // On soumet les données
        $.post($form.attr('action'), data).then(function (data) {
            // On récupère le nouveau <select>
            let $input = $(data).find(target)
            // On remplace notre <select> actuel
            $(target).replaceWith($input)

            if (typeof callback === "function") callback();
        })
    });
}

/**
 *  Permet de limiter le choix du pays des acteurs (company,personDegree et school)
 *  et de simuler un évênement sur le pays pour que la région se mette à jour avec le addCity
 *  Ajout idSelectedRegion pour DBTA dans v325
 */
global.countryEvent = async function countryEvent(idSelectedCountry, type) {
    let idCountry = $(idSelectedCountry).val();
    let nameCountry = $(idSelectedCountry).text().replace(/\n/, '').replace(/ /g, '');

    let idAppbundleTypeCountry = "#appbundle_" + type + "_country";
    let idAppbundleTypeRegion = "#appbundle_" + type + "_region";
    let idAppbundleTypeCity = "#appbundle_" + type + "_city";
    if (type == "persondegree") {
        idAppbundleTypeCity = "#appbundle_" + type + "_addressCity";
    }

    $(idAppbundleTypeCountry + ' option').remove();
    $(idAppbundleTypeCountry).append("<option value=''> </option>");
    $(idAppbundleTypeCountry).append("<option value='" + idCountry + "'>" + nameCountry + "</option>");

    if ($(idAppbundleTypeCity + ' option:selected').val() === "") {
        if ($(idAppbundleTypeCountry + ' option:selected').val() == "") {
            $(idAppbundleTypeCountry).change(function () {
                listenChangeCountryRegion(idAppbundleTypeCountry, idAppbundleTypeRegion, 'country', 'region', 'city');
                $(idAppbundleTypeCountry).css("appearance", "none")
                $(idAppbundleTypeCountry).css("-webkit-appearance", "none")
                $(idAppbundleTypeCountry).css("-moz-appearance", "none")
            });
            $(idAppbundleTypeCountry).val(parseInt(idCountry));
            $(idAppbundleTypeCountry).trigger('change');
            // Fin de simul
        }
    } else {
        $(idAppbundleTypeCountry + " option[ value='" + idCountry + "']").attr("selected", "selected");
        // console.log(idAppbundleTypeCountry + " option[ value='"+ idCountry +"']")
    }
}

/**
 *  Adptation DBTA pour la Structure Provine/Pays
 *  Permet de limiter le choix de la region des acteurs (company,personDegree et school)
 *  et de simuler un évênement sur le pays pour que la région se mette à jour avec le addCity
 *  Ajout idSelectedRegion pour DBTA dans v235
 */
global.regionEvent = function regionEvent(idSelectedCountry, idSelectedRegion, type) {
    let idCountry = $(idSelectedCountry).val();
    let nameCountry = $(idSelectedCountry).text().replace(/\n/, '').replace(/ /g, '');
    let idRegion = $(idSelectedRegion).val();
    let nameRegion = $(idSelectedRegion).text().replace(/\n/, '').replace(/ /g, '');

    let idAppbundleTypeCountry = "#appbundle_" + type + "_country";
    let idAppbundleTypeRegion = "#appbundle_" + type + "_region";
    let idAppbundleTypeCity = "#appbundle_" + type + "_city";
    if (type == "persondegree") {
        idAppbundleTypeCity = "#appbundle_" + type + "_addressCity";
    }

    $(idAppbundleTypeCountry + ' option').remove();
    // $(idAppbundleTypeCountry).append("<option value=''></option>");
    let nameCountryTrans = nameCountry;
    if (translations[nameCountry]) nameCountryTrans = translations[nameCountry];
    let nameRegionTrans = nameRegion;
    if (translations[nameRegion]) nameRegionTrans = translations[nameRegion];
    $(idAppbundleTypeCountry).append("<option value='" + idCountry + "'>" + nameCountryTrans + "</option>");

    $(idAppbundleTypeRegion + ' option').remove();
    $(idAppbundleTypeRegion).append("<option value=''> </option>");
    $(idAppbundleTypeRegion).append("<option value='" + idRegion + "'>" + nameRegionTrans + "</option>");

    if ($(idAppbundleTypeCity + ' option:selected').val() === "") {
        if ($(idAppbundleTypeRegion + ' option:selected').val() == "") {

            $(idAppbundleTypeRegion).change(function () {
                let cityName = "city";
                if (type == "persondegree") {
                    cityName = "addressCity";
                }
                listenChangeCountryRegion(idAppbundleTypeCountry, idAppbundleTypeRegion, 'country', 'region', cityName);
                $(idAppbundleTypeRegion).css("appearance", "none")
                $(idAppbundleTypeRegion).css("-webkit-appearance", "none")
                $(idAppbundleTypeRegion).css("-moz-appearance", "none")
            });
            $(idAppbundleTypeRegion).val(parseInt(idRegion));
            $(idAppbundleTypeRegion).trigger('change');
            $(idAppbundleTypeRegion).change();

            // Fin de simul
        }
    } else {
        $(idAppbundleTypeRegion + " option[ value='" + idRegion + "']").attr("selected", "selected");
        // console.log(idAppbundleTypeCountry + " option[ value='"+ idCountry +"']")
    }
}

global.listenChangeRegionPrefecture = function listenChangeRegionPrefecture(idCountry, idRegion, idPrefecture, idCity) {
    let baseUrl = getUrlCurrentLocale();
    //event on country to search regions and cities
    $(idCountry).on('change', function () {
        let countryId = $(idCountry + " option:selected").val();
        changeCountry(idRegion, idPrefecture, idCity, countryId);
    })

    //event on region to search prefectures and cities
    $(idRegion).on('change', function () {
        let regionId = $(this).val();
        changeRegion(idPrefecture, idCity, regionId, -1)
    })

    //event on prefecture to search cities
    $(idPrefecture).on('change', function () {
        let prefectureId = $(this).val();
        changePrefecture(idCity, prefectureId, -1)
    })

    //event on city to update region and prefecture
    $(idCity).on('change', function () {
        changeCity(idRegion, idPrefecture, idCity, $(this).val());
    })
}

global.createSortedOptions = function (idSelect, inputObject, cityId) {
    // console.log("-->" , Object.keys(result["regions"]).length)
    let counter = 0;
    while (Object.keys(inputObject).length > 0) {
        counter++;
        $.each(inputObject, function (key, value) {
            let minus = true;
            $.each(inputObject, function (key2, value2) {
                if (key != key2) {
                    // console.log(value, value2)
                    if (value2 < value) {
                        minus = false;
                    }
                }
            })
            if (minus == true) {
                if (value.length) {
                    let newOption = new Option(value, key);
                    $(idSelect).append(newOption);
                }
                delete inputObject[key];
            }
        })
        // console.log("counter " + counter + " length=" + Object.keys(inputObject).length)
        if (counter > 1500) {
            alert("Erreur système dans le triage des options")
            break;
        }
    }

    if (cityId) {
        $(idSelect + ' option[value=' + cityId + ']').prop('selected', true);
    }
}


global.changeCountry = function (idRegion, idPrefecture, idCity, countryId) {
    // console.log(idRegion, idPrefecture, idCity, countryId)
    let baseUrl = getUrlCurrentLocale();
    let cityId = $(idCity + " option:selected").val();
    removeOptions(idRegion);
    removeOptions(idPrefecture);
    removeOptions(idCity);
    // let countryId = $(this).val();
    let url = baseUrl + "getRegionsByCountry";

    // console.log("-->",url)
    let data = {"countryId": countryId}

    //transform objects to arrays
    $.get(url, data).done(function (result) {
        // console.log(result)
        createSortedOptions(idRegion, result["regions"], null);
        createSortedOptions(idCity, result["cities"], null);
        $(idCity + " option[id=' " + cityId + "']").attr("selected", "selected");
    });
}

global.changeRegion = function (idPrefecture, idCity, regionId, cityId) {
    // alert("region")
    //console.log(idPrefecture, idCity, regionId, cityId);
    let baseUrl = getUrlCurrentLocale();
    removeOptions(idPrefecture);
    removeOptions(idCity);
    let url = baseUrl + "getPrefecturesByRegion";

    // console.log(url)
    let data = {"regionId": regionId}

    $.get(url, data).done(function (result) {
        createSortedOptions(idPrefecture, result["prefectures"], cityId);
        createSortedOptions(idCity, result["cities"], cityId);
        $(idCity + " option[id=' " + cityId + "']").attr("selected", "selected");
    })
}

global.changePrefecture = function (idCity, prefectureId, cityId) {
    // alert("prefecture")
    let baseUrl = getUrlCurrentLocale();
    let url = baseUrl + "getCitiesByPrefecture";
    removeOptions(idCity);

    // console.log(url)
    let data = {"prefectureId": prefectureId}
    $.get(url, data).done(function (result) {
        createSortedOptions(idCity, result["cities"], cityId);
        $(idCity + " option[id=' " + cityId + "']").attr("selected", "selected");
    })
}

global.changeCity = function (idRegion, idPrefecture, idCity, cityId) {
    // alert("city")
    let baseUrl = getUrlCurrentLocale();
    let url = baseUrl + "getRegionAndPrefectureByCity";

    let data = {"cityId": cityId}
    $.get(url, data).done(function (result) {
        // console.log(result);

        //update region select
        $(idRegion + " option").each(async function () {
            if (result['regionId'] == $(this).val()) {
                // console.log("region " + result['regionId'] , "->" , $(this).val());
                $(this).attr("selected", "selected");
                let regionId = $(this).val();
                await changeRegion(idPrefecture, idCity, regionId, cityId);

                //update prefecture select
                setTimeout(function () {
                    $(idPrefecture + " option").each(function () {
                        // console.log("pref " , result['prefectureId'] , "->" , $(this).val());
                        if (result['prefectureId'] == $(this).val()) {
                            $(this).attr("selected", "selected");
                            let prefectureId = $(this).val();
                            changePrefecture(idCity, prefectureId, cityId)
                        }
                    })
                }, 500);
            }
        })
    })
}


// Event pou la recupération de la liste des diplômes et secteurs d'activités d'une école
// function listenChangeSchoolInputs(idSchoolHTML, idSectorArea, idSelectInput) {
global.listenChangeSchoolInputs = function listenChangeSchoolInputs(idSchoolHTML, idDegree, idSectorArea, idSelectActivity) {
    $(document).on('change', idSchoolHTML, function () {
        changeSchoolInputs(idSchoolHTML, idDegree, idSectorArea, idSelectActivity);
    })
}

// Recupération de la liste des diplômes et secteurs d'activités d'une école
global.changeSchoolInputs = function changeSchoolInputs(idSchoolHTML, idDegree, idSectorArea, idSelectActivity) {
    // $data pour les Activity
    let $data = [];

    // Récupération du school selectionné
    let idSchool = $(idSchoolHTML + ' option:selected').val();


    // on affiche tous les diplômes sauf pour les values vides (Sélectionnez ou Autre Etablissement)
    if (!idSchool) {
        $(idDegree + ' option[value!=""]').prop("hidden", false);
        $(idSectorArea + ' option[value!=""]').prop("hidden", false);
        $(idSelectActivity + ' option[value!=""]').prop("hidden", false);
    } else {
        // Supression de la route edit ou new de l'adresse actuelle
        let dirs = window.location.href.split('/');
        let $locationRef = "";
        for (let i = 0; i < dirs.length - 1; i++)
            $locationRef += dirs[i] + '/';

        // appelle en Ajax les données liées à l'établissement
        console.log('REKKKKKKKK', $locationRef + 'filters/' + idSchool + '/school');
        $.get($locationRef + 'filters/' + idSchool + '/school').done(function (data) {
            // on cache tous les données (diplômes ou secteurs) sauf pour les values vides
            $(idDegree + ' option[value!=""]').prop("hidden", true);
            $(idSectorArea + ' option[value!=""]').prop("hidden", true);

            // on affiche les diplômes de l'établissement
            $.each(data[0], function (key, value) {
                $(idDegree + '  option').each(function () {
                    if ($(this).val() == value.id)
                        $(this).prop("hidden", false);
                })
            })

            // on affiche les secteurs de l'établissement
            $.each(data[1], function (key, value) {
                $(idSectorArea + '  option').each(function () {
                    if ($(this).val() == value.id)
                        $(this).prop("hidden", false);
                })
            })

            // on sauvegarde les activitées de l'établissement
            $data = data[2]
            // console.log("TEST222->", $data)
            $(idSelectActivity + '  option').each(function () {
                let optionExist = false
                let optionSelectActivity = $(this);
                $.each(data[2], function (key, value) {
                    if (optionSelectActivity.val() == value.id) {
                        optionExist = true;
                    }
                })
                if (optionExist == false) {
                    optionSelectActivity.prop("hidden", true);
                }
            })
        })
    }
    $(document).on('change', idSectorArea, function test() {
        if ($(idSchoolHTML + ' option:selected').val() == "") {
            $(idSelectActivity + ' option[value!=""]').prop("hidden", false);
        } else {
            $(idSelectActivity + ' option[value!=""]').prop("hidden", true);
            setTimeout(function () {
                $.each($data, function (key, value) {
                    $(idSelectActivity + '  option').each(function () {
                        if ($(this).val() == value.id)
                            $(this).prop("hidden", false);
                    })
                })
            }, 500);
        }
    })
}

global.validPersonDegreeBySchool = function validPersonDegreeBySchool(button, idPersonDegree, value) {
    // alert(window.location.href);

    // changement de la value
    if (value == 0) {
        value = 1
    } else if (value == 1) {
        value = 0
    }

    //creation de la data pour json
    let data = {"checkSchool": value};
    let immat = "#immat" + idPersonDegree;
    let buttonId = "#" + button + idPersonDegree;

    // appel ajax en get
    $.get(window.location.href + '/' + idPersonDegree + '/checkPersonDegree', data).done(function () {
        if (value == 1) {
            $(buttonId).val(1);
            $(buttonId).text('Dévalider');
            $(immat).css("color", "green");
        } else {
            $(buttonId).val(0);
            $(buttonId).text('Valider');
            $(immat).css("color", "red");
        }
    });
}

global.updateCompanySchool = function updateCompanySchool(classCheckBox, idCompany) {
    // Supression de la route all_companies de l'adresse actuelle
    let dirs = window.location.href.split('/');
    let locationRef = "";
    for (let i = 0; i < dirs.length - 1; i++)
        locationRef += dirs[i] + '/';

    // récupération de l'état du checkbox
    let idCheckBox = "#companySchool" + idCompany;
    let stateCheckBox = $(idCheckBox).is(':checked') ? 1 : 0;

    // appel ajax en get
    let data = {"isCompany": stateCheckBox};
    $.get(locationRef + 'companies/' + idCompany + '/updateCompany', data).done(function () {
    });
}

global.changeSchoolActivities = function changeSchoolActivities(idSchoolHTML, idSectorArea, idSelectActivity, data) {
    // on affiche toutes les activitées sauf pour les values vides (Sélectionnez ou Autre Etablissement)
    if ($(idSectorArea + ' option:selected').val() == "") {
        $(idSelectActivity + ' option[value!=""]').prop("hidden", false);
    } else {
        $.each(data, function (key, value) {
            // console.log(key + '->' +value);
        })
    }
}

/**
 * permet de créer un donut d'un acteur en fonction d'une table
 * @param actorName
 * @param tableName
 */
global.donutCreation = function donutCreation(actorName, tableName, translations) {
    let labelsTableDataName = [];

    $('#' + actorName + tableName + 'CheeseData' + ' option').each(function () {
        let text = $(this).text();
        if (translations[text]) text = translations[text];
        labelsTableDataName.push(text);
    });

    let labelsTableDataValue = [];
    $('#' + actorName + tableName + 'CheeseData' + ' option').each(function () {
        labelsTableDataValue.push($(this).val());
        // console.log("value=" + $(this).val());
    });

    let labelsTableColorBack = [];
    $('#' + actorName + tableName + 'CheeseColor' + ' option').each(function () {
        labelsTableColorBack.push($(this).val());
    });

    let labelsTableColorHover = [];
    $('#' + actorName + tableName + 'CheeseColor' + ' option').each(function () {
        labelsTableColorHover.push($(this).text());
    });

    /*Affichage Donut Activite Area */
    /********************************/
    if ($("#donut" + actorName + tableName).length) {
        var u = $("#donut" + actorName + tableName), h = {
            labels: labelsTableDataName,
            datasets: [{
                data: labelsTableDataValue,
                backgroundColor: labelsTableColorBack,
                hoverBackgroundColor: labelsTableColorHover,
                borderWidth: 0
            }]
        };
        new Chart(u, {
            type: "doughnut",
            data: h,
            options: {legend: {display: !1}, animation: {animateScale: !0}, cutoutPercentage: 80}
        })
    }
}

/**
 * permet de créer un graph Baton
 * @param actorName
 * @param tableName
 */
global.graphCreation = function graphCreation(actorName, tableName, translations) {

    let labelsTableDataName = [];
    $('#' + actorName + tableName + 'GraphData' + ' option').each(function () {
        let text = $(this).text();
        if (translations[text]) text = translations[text];
        labelsTableDataName.push(text);
    });

    let labelsTableDataValue = [];
    $('#' + actorName + tableName + 'GraphData' + ' option').each(function () {
        labelsTableDataValue.push($(this).val());
        // console.log($(this).val());
    });

    let labelsTableColorBack = [];
    $('#' + actorName + tableName + 'GraphColor' + ' option').each(function () {
        labelsTableColorBack.push($(this).val());
    });

    let labelsTableColorBorder = [];
    $('#' + actorName + tableName + 'GraphColor' + ' option').each(function () {
        labelsTableColorBorder.push($(this).text());
    });

    /*Affichage Graph Activite Area */
    /********************************/
    if ($("#" + actorName + tableName + "Graph").length) {
        var d = $("#" + actorName + tableName + "Graph"), c = {
            labels: labelsTableDataName,
            datasets: [{
                backgroundColor: labelsTableColorBack,
                borderColor: labelsTableColorBorder,
                borderWidth: 1,
                data: labelsTableDataValue
            }]
        };
        new Chart(d, {
            type: "bar",
            data: c,
            options: {
                scales: {
                    xAxes: [{
                        display: !1,
                        ticks: {fontSize: "11", fontColor: "#969da5"},
                        gridLines: {color: "rgba(0,0,0,0.05)", zeroLineColor: "rgba(0,0,0,0.05)"}
                    }],
                    yAxes: [{
                        ticks: {beginAtZero: !0},
                        gridLines: {color: "rgba(0,0,0,0.05)", zeroLineColor: "#6896f9"},
                    }]
                }, legend: {display: !1}, animation: {animateScale: !0}
            }
        })
    }
}

/**
 * permet de créer un graphe temporel d'une activité de questionnaires en fonction d'une durée
 * @param actorName
 * @param duration
 */
global.timeGraphCreation = function timeGraphCreation(type, actorName, duration) {
    /* Recupération des datas */
    let labelsTableDataName = [];
    let labelsTableDataValue = [];
    let maxValue = 0;

    $('#' + type + actorName + 'DataChart' + ' option').each(function () {
        labelsTableDataValue.push($(this).text());
        labelsTableDataName.push($(this).val());

        if (parseInt($(this).text()) > maxValue)
            maxValue = parseInt($(this).text());
    });
    maxValue += 5;

    /* Affichage lineChartDegree */
    let lineChartName = "#lineChart" + type + actorName;
    if ($(lineChartName).length) {
        let i = $(lineChartName),
            n = {
                labels: labelsTableDataName,
                datasets: [{
                    label: "Nombre",
                    fill: !1,
                    lineTension: 0,
                    backgroundColor: "#fff",
                    borderColor: "#6896f9",
                    borderCapStyle: "butt",
                    borderDash: [],
                    borderDashOffset: 0,
                    borderJoinStyle: "miter",
                    pointBorderColor: "#fff",
                    pointBackgroundColor: "#2a2f37",
                    pointBorderWidth: 3,
                    pointHoverRadius: 10,
                    pointHoverBackgroundColor: "#FC2055",
                    pointHoverBorderColor: "#fff",
                    pointHoverBorderWidth: 3,
                    pointRadius: 6,
                    pointHitRadius: 10,
                    data: labelsTableDataValue,
                    spanGaps: !1
                }]
            }, s = new Chart(i, {
                type: "line",
                data: n,
                options: {
                    legend: {display: !1},
                    scales: {
                        xAxes: [{
                            ticks: {fontSize: "10", fontColor: "#969da5"},
                            gridLines: {color: "rgba(0,0,0,0.05)", zeroLineColor: "rgba(0,0,0,0.05)"}
                        }], yAxes: [{display: !1, ticks: {beginAtZero: !0, max: maxValue}}]
                    }
                }
            });
    }
}

global.datatable2 = function datatable2(retrieve = false) {
    let splitPath = window.location.toString().split('/' + getCurrentLocale() + '/');
    let jsonLangage = splitPath[0] + '/locale/' + getCurrentLocale().toLowerCase() + '_' + getCurrentLocale().toUpperCase() + '.json'

    let options = {
        language: {
            url: jsonLangage
        },
        initComplete: function () {
            $('#kz_table2_wrapper input').addClass('form-control form-control-sm kz_table2_input')
            $('#kz_table2_wrapper select').addClass('form-control form-control-sm kz_table2_select')
            $('#kz_table2_wrapper .row').css('width', '100%')
            $('#kz_table2').parent().css('width', '100%')
        },
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    };
    if (retrieve == true) {
        options = {retrieve: true}
    }
    return $('#kz_table2').DataTable(options);
}

global.datatableWithExport = function () {
    $(document).ready(function () {
        $('#kz_table_with_export').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
    });
}

global.printDashboardToPDF = function printDashboardToPDF(e) {
    $('#printDashboardLoading').removeAttr('hidden');
    e.preventDefault();

    //var node = document.getElementById('dashboardToPrint');
    var node = document.getElementById('dashboardToPrint');
    var width = $('#dashboardToPrint').width();
    var height = $('#dashboardToPrint').height();
    var options = {
        quality: 1
    };

    /*domToImage.toPng(node, options)
        .then(function (blob) {
           var pdf = new jsPDF('p', 'mm', [(width + 50), height]);

           pdf.addImage(blob, 'PNG', 20, 10, width, height);
           pdf.save($('title').text() + '.pdf');
           console.log('enddDd....');
           $('#printDashboardLoading').attr('hidden', 'hidden');
        });*/

    // with higth quality
    var scale = 2;
    var options = {
        width: node.clientWidth * scale,
        height: node.clientHeight * scale,
        style: {
            transform: 'scale(' + scale + ')',
            transformOrigin: 'top left'
        }
    };

    domToImage.toPng(node, options)
        .then(function (blob) {
            var pdf = new jsPDF('p', 'mm', [(width + 160), height]);

            pdf.addImage(blob, 'PNG', 20, 10, (width + 40), height);
            // pdf.save("test-png.pdf");
            pdf.save($('title').text() + '.pdf');
            $('#printDashboardLoading').attr('hidden', 'hidden');
        });
}

global.getBaseUrl = function () {
    let dirs = window.location.href.split('/');
    let $locationRef = '';

    for (let i = 0; i < dirs.length - 1; i++) {
        $locationRef += dirs[i] + '/';
    }

    return $locationRef;
}

global.getCurrentLocale = function () {
    /* recupération de la locale de la route courante : 1ere occurence de 2 caracteres */
    let locale = "";
    let splitPath = window.location.toString().split('/');
    for (let i = 0; i < splitPath.length; i++) {
        if (splitPath[i].length == 2) {
            locale = splitPath[i];
            i = splitPath.length;
        }
    }
    return (locale);
}

global.getUrlCurrentLocale = function () {
    let locale = '/' + getCurrentLocale() + '/';
    let baseUrl = window.location.toString();
    let indexLocale = baseUrl.indexOf(locale) + 4;
    baseUrl = baseUrl.substring(0, indexLocale);

    return baseUrl;
}

// Récupération des données de traduction des fichiers messages.pays.xlf
global.getTranslation = async function () {

    let locale = '/' + getCurrentLocale() + '/';
    // console.log(locale);
    let url = window.location.toString();
    let indexLocale = url.indexOf(locale) + 4;
    url = url.substring(0, indexLocale) + "get_js_translation";
    // console.log(url);

    let result = {};

    // appel ajax en get
    await $.get(url).done(function (res) {
        console.log(res);
        result = res;
    })

    return result;
}

// Déconnexion de l'utilisateur après un certain temps d'inactivité.
let timeout;
global.startTimeout = function () {
    timeout = setTimeout(() => {
        // Dispatch the event
        checkInactivity();
    }, 3600000); // 1mn = 60000 ms; 10 minutes = 600000 ms; 30 minutes = 1800000
}

// Reset the timeout when the user is active
document.onmousemove = document.onkeypress = () => {
    // console.log('user is active::::');
    clearTimeout(timeout);
    startTimeout();
};

global.checkInactivity = function () {
    let locale = $('#inputLocale').val();
    $.post(`/${locale}/dispatch-session-timeout-event`, []).then(function (response) {
        if (response.status !== undefined && response.status === 'success') {
            window.location.href = `/${locale}/logout`;
        }

    })
}

global.removeOptions = function (idSelect) {
    $(idSelect + " option").each(function () {
        if ($(this).val()) {
            $(this).remove();
        }
    })
}


startTimeout();
