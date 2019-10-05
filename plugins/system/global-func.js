// Clear form
function ClearForm(id) {
	window.setTimeout(function() {
		id.elements.text.value = '';
	}, 100);
}

// Add favorites
function Bookmark(sitename, homeurl) {
	if (window.sidebar) {
		window.sidebar.addPanel(sitename, homeurl, "");
	} else if (document.all) {
		window.external.AddFavorite(homeurl, sitename);
	}
}

// Location href self
function Location(url) {
	window.location.href = url;
}

// Mail protect from spam bots
String.prototype.AddMail = function (prefix, postfix) {
	hamper = prefix+"@"+postfix;
	document.write((hamper).link("mailto:"+hamper));
}

// Delete check info
function DelCheck(form, text) {
	check = confirm(text);
	if (check == false) return false;
}

// Open window
function OpenWindow(url, title, x, y) {
	window.open(url, title, "toolbar=0, location=0, directories=0, status=0, scrollbars=0, resizable=1, copyhistory=0, width="+x+", height="+y+"");
}

// Adding products in shop module
var flyingSpeed = 10;
var shop_div = false;
var flyingDiv = false;
var currentProductDiv = false;
var shop_x = false;
var shop_y = false;
var slide_xFactor = false;
var slide_yFactor = false;
var diffX = false;
var diffY = false;
var currentXPos = false;
var currentYPos = false;

function ShopCartTop(inputObj) {
	var returnValue = inputObj.offsetTop;
	while ((inputObj = inputObj.offsetParent) != null) {
		if (inputObj.tagName != 'HTML') returnValue += inputObj.offsetTop;
	}
	return returnValue;
}

function ShopCartLeft(inputObj) {
	var returnValue = inputObj.offsetLeft;
	while ((inputObj = inputObj.offsetParent) != null) {
		if (inputObj.tagName != 'HTML')returnValue += inputObj.offsetLeft;
	}
	return returnValue;
}

function AddBasket(productId) {
	if (!shop_div)shop_div = document.getElementById('shop');
	if (!flyingDiv) {
		flyingDiv = document.createElement('DIV');
		flyingDiv.style.position = 'absolute';
		document.body.appendChild(flyingDiv);
	}
	shop_x = ShopCartLeft(shop_div);
	shop_y = ShopCartTop(shop_div);
	currentProductDiv = document.getElementById('sliding' + productId);
	currentXPos = ShopCartLeft(currentProductDiv);
	currentYPos = ShopCartTop(currentProductDiv);
	diffX = shop_x - currentXPos;
	diffY = shop_y - currentYPos;
	var shoppingContentCopy = currentProductDiv.cloneNode(true);
	shoppingContentCopy.id = '';
	flyingDiv.innerHTML = '';
	flyingDiv.style.left = currentXPos + 'px';
	flyingDiv.style.top = currentYPos + 'px';
	flyingDiv.appendChild(shoppingContentCopy);
	flyingDiv.style.display='block';
	flyingDiv.style.width = currentProductDiv.offsetWidth + 'px';
	FlyBasket(productId);
}

function FlyBasket(productId) {
	var maxDiff = Math.max(Math.abs(diffX),Math.abs(diffY));
	var moveX = (diffX / maxDiff) * flyingSpeed;
	var moveY = (diffY / maxDiff) * flyingSpeed;
	currentXPos = currentXPos + moveX;
	currentYPos = currentYPos + moveY;
	flyingDiv.style.left = Math.round(currentXPos) + 'px';
	flyingDiv.style.top = Math.round(currentYPos) + 'px';
	if (moveX > 0 && currentXPos > shop_x) flyingDiv.style.display='none';
	if (moveX < 0 && currentXPos < shop_x) flyingDiv.style.display='none';
	if (flyingDiv.style.display=='block') setTimeout('FlyBasket("' + productId + '")', 10);
}