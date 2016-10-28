jQuery(document).ready(function(){
	jQuery(".sucom_tooltip").qtip({
		content:{
			attr:'alt',
		},
		position:{
			my:'bottom left',
			at:'top center',
		},
		show:{
			when:{
				event:'mouseover',
			},
		},
		hide:{
			fixed:true,
			delay:300,
			when:{
				event:'mouseleave',
			},
		},
		style:{
			tip:{
				corner:true,
			},
			classes:'sucom-qtip qtip-blue',
			width:500,
		},
	});
});
