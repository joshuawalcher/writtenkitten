var current_word_count = 0;
var words_for_reward = 100;
var kittens_earned = 0;
var kittens_shown = 0;
var warning_shown = false;
var search_for = 'kitten';
var flickr_search_term = "kitten,cute"

var valid_licenses="4,5,7";

/*
4 - Attribution License (http://creativecommons.org/licenses/by/2.0/)
5 - Attribution-ShareAlike License (http://creativecommons.org/licenses/by-sa/2.0/)
7 - No known copyright restrictions (http://flickr.com/commons/usage/)
*/

/**
 * @type {string}
 */
var warning = "#warning-ls";
if (typeof localStorage == "undefined") {
	warning = "#warning-no-ls";
} else {
	warning = "#warning-ls";
}

var next_kitten = {
	img_url: '',
	page_url: '',
	alt: '',
	sharefb: '',
	sharetwitter: ''
};

//Get license info
const license_url = "https://api.flickr.com/services/rest/?format=json&method=flickr.photos.licenses.getInfo&api_key=5dfc80756edad8d0566cf40f0909324e&jsoncallback=?";
const custom_shorts = {"No known copyright restrictions": "Flickr Commons"};

/**
 * @type {{name: string,url: string,shortname: string}[]}
 */
var license_list = [];


async function updateLicenses()
{
  const licenseResponse = await fetch(license_url);
  const licenseData = await licenseResponse.json();
  console.log("license\n" + JSON.stringify(licenseData))
  document.querySelectorAll(licenseData.licenses.license).forEach((el, i) =>
  {
    
    let licdata = { "name": el.name, "url": el.url, "shortname": "" };
    let shortcc = licdata.url.match(/creativecommons\.org\/licenses\/([^\/]+)\//);
    
    //Assign short
    if (shortcc)
    {
      licdata["shortname"] = 'CC-' + shortcc[1].toUpperCase();
    } else if (Object.keys(custom_shorts).includes(licdata.name))
    {
      licdata["shortname"] = custom_shorts[licdata.name]; 
    } else {
      licdata["shortname"] = licdata.name;
    }

    license_list[el.id] = licdata;
  }

)
}




/**
 * @param {string} text
 * @param {{ value: any; }} wc
 */
function word_count(text, wc) {
	if (typeof localStorage != "undefined") {
		localStorage.text = text;
	}
	
	if (current_word_count >= 10 && warning_shown == false) {
		show_warning();
	}
	
	text = text.replace(/^\s*|\s*document.querySelector/g,''); //removes whitespace from front and end
	text = text.replace(/\s+/g,' '); // collapse multiple consecutive spaces
	var words = text.split(" ");
	wc.value = words.length;
	current_word_count = wc.value = words.length;
	document.querySelector("#displayWords").innerHTML = wc.value;
	kittens_earned = current_word_count / words_for_reward;
	if (kittens_earned >= ((kittens_shown*1)+1)) {
		
		if(current_word_count > 90 && current_word_count < 110){
			if(document.querySelector('#search').value != search_for){
				search_for = document.querySelector('#search').value;
				flickr_search_term = document.querySelector('#search').value; + ',cute';
			}
			fetch_next_kitten();
		}
		
		show_kitten();
		
	}
}

function show_warning() {
  document.querySelector(warning).classList.replace('hide', 'show');
}

/**
 * @param {boolean} immediate
 */
function hide_warning(immediate) {
	if (immediate == true) {
    document.querySelector(warning).style.display = 'none';
	} else {
    document.querySelector(warning).classList.replace('show', 'hide');
	}
	warning_shown = true;
}

async function show_kitten() {
	hide_warning(true);
	kittens_shown++;
	document.querySelector("#kittenFrame").css("background-image", "url(" + next_kitten.img_url + ")");
	console.log(next_kitten.img_url);
	document.querySelector("#kittenCredit").innerHTML = ("<a href='" + next_kitten.page_url + "'>" + next_kitten.alt + "</a><br><a href='" + next_kitten.sharefb + "'>Share on Facebook</a><br><a href='" + next_kitten.sharetwitter + "'>Share on Twitter</a>");
	console.log(document.querySelector("#kittenCredit").innerHTML);
	await fetch_next_kitten();
}

async function fetch_next_kitten() {
	if (getParameterByName("search")) {
		// if they are using a URL param, take them very literally. They
		// generally know what they're doing.
		search_for = getParameterByName("search");
		flickr_search_term = search_for + ',cute';
	} else {
		// add "cute" to search if item is selected from dropdown. it just
		// works better that way.
		flickr_search_term = search_for;
	}

	var flickr_url = "https://api.flickr.com/services/rest/?api_key=0fa41c7f9709b1d850756011e09bbef6&format=json&sort=interestingness-desc&method=flickr.photos.search&license=" + valid_licenses + "&extras=owner_name,license&tags=" + flickr_search_term + "&tag_mode=all&jsoncallback=?";

  
  const flickerResponse = await fetch(flickr_url);
  const flickerData = await flickerResponse.json();
  console.log("flicker\n" + JSON.stringify(flickerData))
  var i = Math.ceil(Math.random() * flickerData.photos.photo.length);
  var photo = flickerData.photos.photo[i];
  console.log(photo);
  if (photo.ownername) {
    if (photo.ownername == "jus10h" || photo.ownername == "evaindia" || photo.ownername == "gailhampshire" || photo.ownername == 'gails_pictures' || photo.ownername.includes('rachel swallow') || photo.ownername == 'falconghost' || photo.ownername == 'sebilden' || photo.title.includes('AI artwork')) {
      //console.log('skipping banned owners ' + photo.ownername);
      fetch_next_kitten();
      return false;
    }
  }

  var attrib = "";
  let license = license_list[photo.license];
  if (license_list[photo.license]) {
    if (license.url) {
      attrib = " (<a href=\"" + license.url + "\">" + license.shortname + "</a>)";
    } else {
      attrib = " (" + license.shortname + ")";
    }
  }
  next_kitten.img_url = "http://farm" + photo.farm + ".static.flickr.com/" + photo.server + "/" + photo.id + "_" + photo.secret + "_z.jpg";
  next_kitten.page_url = "http://www.flickr.com/photos/" + photo.owner + "/" + photo.id;
  next_kitten.alt = photo.title + " by " + photo.ownername + attrib;
  next_kitten.sharefb = 'https://www.facebook.com/sharer.php?u=' + next_kitten.page_url;
  next_kitten.sharetwitter = 'https://twitter.com/intent/tweet?url=' + next_kitten.page_url + '&text=Check out the cute ' + search_for + ' I found on writtenkitten.co!';
  document.querySelector("#nextKitten")?.setAttribute("src", next_kitten.img_url);
}

/**
 * @param {number} howmany
 */
function set_reward(howmany) {
	words_for_reward = howmany;
	kittens_earned = current_word_count / howmany;
	kittens_shown = kittens_earned;
}

/**
 * @param {string} searchTerm
 */
function set_search(searchTerm) {
  let tmp = getParameterByName("search");
	if (tmp) {
		tmp.replace(/</g, "&lt;").replace(/>/g, "&gt;"); // sanitize
		search_for = tmp;
		flickr_search_term = tmp + ',cute';
	} else {
		search_for = searchTerm + ',cute';
	}
	
	set_title();
}

function set_title() {
	if (search_for != "kitten,cute") {
    document.querySelector("#titleKitten").innerHTML = ("<strike>Kitten!</strike>");
		document.querySelector("#titleSearch").innerHTML = ("&nbsp;" + search_for.replace(',cute','') + "!");
	}
	else {
		document.querySelector("#titleKitten").innerHTML = ("Kitten!");
		document.querySelector("#titleSearch").innerHTML = ("");
	}
}

/**
 * @param {string} name
 */
function getParameterByName(name) {
	name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
	var regexS = "[\\?&]" + name + "=([^&#]*)";
	var regex = new RegExp(regexS);
	var results = regex.exec(window.location.href);
	if (results == null) {
		return "";
	} else {
		return decodeURIComponent(results[1].replace(/\+/g, " "));
	}
}

function restore_text() {
	if (typeof localStorage != "undefined") { 
		document.querySelector("#writearea")?.setAttribute("value", localStorage.text);
	}
}

/**
 * @param {{ keyCode: number; target: any; preventDefault: () => void; }} e
 */
function tabs(e) {
	if(e.keyCode === 9) { // if tab
		var el = e.target;

		// get caret position or selection
		var start = el.selectionStart;
		var end = el.selectionEnd;
		
		var value = el.value;
		
		// set text to: text before caret + tab + text after caret
		el.value = value.substring(0, start
					+ "\t"
					+ value.substring(end));
		
		// reset caret position
		el.selectionStart = el.selectionEnd = start + 1;
		
		// keep focus
		e.preventDefault();
	}
}

async function initPage() {
	set_search(document.querySelector('#search').value);
	set_title();
  restore_text();
  await updateLicenses()
}

