_ = new Object();
_.token = $('meta[name="csrf-token"]').attr('content');
_.domain = "/";
_.locale = $('html').attr('lang');
_.ajaxurl = _.domain + (_.locale.length ? _.locale + "/" : "") + 'ajax';
_.bootloader = '<div class="bootloader no-image"><span class="process"><div class="spinner-wrapper"></div></span></div>';
_.ajax = function(req, callback) {
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
_.ajaxy = function(req, callback) {
  $.ajax({
    url: this.ajaxurl,
    type: 'post',
    data: req,
    headers: { "X-CSRF-TOKEN" : this.token },
    dataType: 'json',
    cache: false,
    contentType: false,
    processData: false,
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
_.blackListTable = function(page, parent) {
	page = page || 1;
	parent = parent || $('#black-list-table');
	let pagination = parent.find('.table-pagination');
	let tbody = parent.find('tbody');
	let bloader = $(this.bootloader);
	let keyword = $('#black-list-search').val() || '';
	parent.css({'position':'relative'}).append(bloader);
	this.ajax({action:'blackList',search: keyword, page: page}, function(err, res) {
		if(res) {
			tbody.html(res.html);
			pagination.html(res.pagination);
		} else {
			tbody.html($('<tr class="text-center">').html($('<td colspan="99">').html(err.message)));
		}
		bloader.remove();
	});
};
_.blackListRequest = function(e) {
	if(!e) return;
	e.preventDefault();
	var form = $(e.currentTarget).closest('form');
	var response = form.find('.response');
	var iusername = form.find('[name="username"]');
	var iemail = form.find('[name="email"]');
	var icomment = form.find('[name="comment"]');
	var ifiles = form.find('[name="files[]"]');
	form.find('*').removeClass('required');
	if(!iusername.val()) return iusername.focus().parent().addClass('required');
	if(!iemail.val().isEmail()) return iemail.focus().parent().addClass('required');
	if(!ifiles[0].files.length) return form.find('[name="files[]"]').focus().parent().addClass('required');

	var formData = new FormData(form[0]);
	formData.set('action', 'blackListRequest');
	form.addClass('submited');

	this.ajaxy(formData, function(err, res) {
		if(res) {
			response.html(res.message);
			form[0].reset();
		} else {
			response.html(err.message);
		}
		setTimeout(function() {
			form.removeClass('submited').find('.dropzone').removeClass('dropped');
			response.html(response.attr('data-default'));
		}, 4000);
	});

}
_.boostersTopTable = function(parent) {
	let tbody = parent.find('tbody');
	let tamount = $('#boosters-payout-amount');
	let tcurrency = $('#boosters-payout-currency');
	let bloader = $(this.bootloader);
	parent.css({'position':'relative'}).append(bloader);
	this.ajax({action:'boostersTop'}, function(err, res) {
		if(res) {
			tbody.html(res.html);
			tamount.html(res.total);
			tcurrency.html(res.currency);
		} else {
			tbody.html($('<tr class="text-center">').html($('<td colspan="99">').html(err.message)));
		}
		bloader.remove();
	});
};
_.boosterRequest = function(e) {
	if(!e) return;
	e.preventDefault();
	var form = $(e.currentTarget).closest('form');
	var response = form.find('.response');
	var igame = form.find('[name="game"]');
	var iusername = form.find('[name="username"]');
	var iemail = form.find('[name="email"]');
	var inickname = form.find('[name="nickname"]');
	var ivkontakte = form.find('[name="vkontakte"]');
	var ifacebook = form.find('[name="facebook"]');
	var idiscord = form.find('[name="discord"]');
	var iskype = form.find('[name="skype"]');
	var itelegram = form.find('[name="telegram"]');
	var iexp = form.find('[name="exp"]:checked');
	var iexp_source = form.find('[name="exp_source"]');
	var iplay_hours = form.find('[name="play_hours"]');
	var iplay_week = form.find('[name="play_week"]');
	var icomment = form.find('[name="comment"]');
	form.find('*').removeClass('required');
	if(!parseInt(igame.val())) return igame.focus().parent().addClass('required');
	if(!iusername.val()) return iusername.focus().parent().addClass('required');
	if(!iemail.val().isEmail()) return iemail.focus().parent().addClass('required');
	if(!idiscord.val().length && !iskype.val().length && !itelegram.val().length) return idiscord.focus().parent().addClass('required');
	var req = {
		action: 'boosterRequest',
		game: igame.val() || null,
		username: iusername.val() || null,
		email: iemail.val() || null,
		nickname: inickname.val() || null,
		vkontakte: ivkontakte.val() || null,
		facebook: ifacebook.val() || null,
		discord: idiscord.val() || null,
		skype: iskype.val() || null,
		telegram: itelegram.val() || null,
		exp: iexp.val() || null,
		exp_source: iexp_source.val() || null,
		play_hours: iplay_hours.val() || null,
		play_week: iplay_week.val() || null,
		comment: icomment.val() || null,
	};
	form.addClass('submited');
	this.ajax(req, function(err, res) {
		if(res) {
			response.html(res.message);
			form[0].reset();
		} else {
			response.html(err.message);
		}
		setTimeout(function() {
			form.removeClass('submited');
			response.html(response.attr('data-default'));
		}, 4000);
	});

}

_.partnerRequest = function(e) {
	if(!e) return;
	e.preventDefault();
	var form = $(e.currentTarget).closest('form');
	var response = form.find('.response');
	var igames = form.find('[name="games[]"]:checked');
	var games = igames.map(function() { return parseInt(this.value); }).get();
	var iusername = form.find('[name="username"]');
	var iemail = form.find('[name="email"]');
	var iservice = form.find('[name="service_link"]');
	var icomment = form.find('[name="comment"]');
	form.find('*').removeClass('required');
	if(!iusername.val()) return iusername.focus().parent().addClass('required');
	if(!iservice.val().isUrl()) return iservice.focus().parent().addClass('required');
	if(!iemail.val().isEmail()) return iemail.focus().parent().addClass('required');
	if(!games.length) return form.find('[name="games[]"]:enabled').parent().addClass('required');
	var req = {
		action: 'partnerRequest',
		games: games,
		username: iusername.val() || null,
		email: iemail.val() || null,
		service: iservice.val() || null,
		comment: icomment.val() || null,
	};
	form.addClass('submited');
	this.ajax(req, function(err, res) {
		if(res) {
			response.html(res.message);
			form[0].reset();
		} else {
			response.html(err.message);
		}
		setTimeout(function() {
			form.removeClass('submited');
			response.html(response.attr('data-default'));
		}, 4000);
	});

}

$(document).ready(function() {
	if($('#black-list-table').length) {		
		_.blackListTable(1, $('#black-list-table'));
	}
	if($('#boosters-top-table').length) {		
		_.boostersTopTable($('#boosters-top-table'));
	}
	if($('.games-selector').length) {
	    $('.games-selector .item').on('click',function(e) { 
	    	e.preventDefault();
	    	$(this).closest('.games-selector').find('.item').removeClass('active');
	     	$(this).addClass('active');
	   	});		
	}
	if($('.games-selector .slick').length) {
		var show = parseInt($('.games-selector .slick').attr('data-shown'))||6;
		var autoplay = $('.games-selector .slick').attr('data-autoplay')=='true'?true:false;
		var dots = $('.games-selector .dots').length?true:false;
		$('.games-selector .slick').slick({
			infinite: autoplay,
			autoplay: autoplay,
			autoplaySpeed: 2000,
			dots: dots,
			appendDots:'.games-selector .dots',
			slidesToShow: show,
			slidesToScroll: 1,
			arrows: true,
			prevArrow:'.games-selector .prev',
			nextArrow:'.games-selector .next',
			responsive: [{
			    breakpoint: 1024,
			    settings: { slidesToShow: 5, slidesToScroll: 1, infinite: false }
		    },{
		      	breakpoint: 800,
			    settings: { slidesToShow: 4, slidesToScroll: 1, infinite: false }
		    },{
		      	breakpoint: 600,
			    settings: { slidesToShow: 2, slidesToScroll: 1, infinite: false }
		    }]
		});
	}
	$('.dropzone').on('dragover', function() {
	    $(this).addClass('hover');
	});
	$('.dropzone').on('dragleave', function() {
	    $(this).removeClass('hover');
	});
	$('.dropzone input').on('change', function(e) {
	    var files = this.files;
	    var valid = [];
	    $('.dropzone').removeClass('hover');
	    if(!files) {
	    	this.value = '';
	    	$('.dropzone').removeClass('dropped');
	    	return;
	    }
	    for (var i = 0, file; file = files[i]; i++) {
		   if((/^image\/(gif|png|jpeg)$/i).test(file.type) && (this.accept && $.inArray(file.type, this.accept.split(/, ?/)) >= 0)) {
		   	valid.push(file);
		   }
	    }
	    if(!valid.length) {
	    	$('.dropzone').removeClass('dropped');
	    } else {
	    	$('.dropzone').addClass('dropped');
	    }
	    $('.dropzone img').remove();
    	for(var i = 0, file; file = valid[i]; i++) {
	      	var reader = new FileReader(file);
	      	reader.readAsDataURL(file);
	      	reader.onload = function(e) {
	        	var data = e.target.result, $img = $('<img />').attr('src', data);
	        	$('.dropzone .cover').append($img);
	      	};
	    }
	});
});

String.prototype.isEmail = function() {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(this);
};

String.prototype.isUrl = function() {
 	var regex = /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/;
    return regex.test(this);
};