/**
 * Ferret CMS URL Variables used to retrieve GET data from URL
 * 
 * FerretCMS is a simple lightweight content management system using PHP and MySQL.
 * This CMS class is written purely in PHP and JavaScript.
 *
 * @author Jacob Rogaishio
 * 
 */
function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}
