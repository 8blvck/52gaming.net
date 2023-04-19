app = new Object;
app.token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
app.locale = document.querySelector('html').getAttribute('lang');
app.domain = "/";
app.ajaxurl = app.domain + (app.locale.length ? app.locale + "/" : "") + 'ajax';
app.ajax = function(req, callback) {
  $.ajax({
    url: this.ajaxurl,
    type: 'post',
    data: req,
    headers: { "X-CSRF-TOKEN" : this.token },
    dataType: 'json',
    success: function (data) {
      if(data.status=='ok')
        callback(null, data);
      else
        callback(data, null);
    },
    error: function(data) {
    	callback(null, null);
    }
  });
};
app.http = function(req, cb) {
	var xhr = new XMLHttpRequest();
	xhr.onload = function () {
		if (xhr.status >= 200 && xhr.status < 300) {
			// What do when the request is successful
			console.log('success!', xhr);
		} else {
			// What do when the request fails
			console.log('The request failed!');
		}
		console.log('This always runs...');
	};
	xhr.open('POST', this.ajaxurl);
	xhr.send();	
}
app.blackListTable = function(page, container) {
	page = page || 1;
	let pagination = document.getElementById('app-orders-list-pagination');
	this.http({action:'blackList', page: page}, function(err, res) {
		if(res) {
			container.innerHTML = res.html;
			pagination.innerHTML = res.pagination;
		} else {
			container.innerHTML = err.message;
		}
	});
};
if(document.getElementById('app-orders-list').length) {		
	app.blackListTable(1, document.getElementById('app-orders-list'));
}