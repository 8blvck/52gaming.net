<footer>
	<div class="container">
		<div class="flex align-middle">
			<div class="column">
				<div class="copyright">
					{{ __($_page->copyrights) }} 
				</div>
			</div>
			<div class="column shrink">
				<div class="socials clearfix">
					@if(strlen(trim($_contacts->vkontakte)))
					<a target="_blank" href="{{ $_contacts->vkontakte }}" class="circled"><i class="xicons icon-vk"></i></a>
					@endif
					@if(strlen(trim($_contacts->facebook)))
					<a target="_blank" href="{{ $_contacts->facebook }}" class="circled"><i class="xicons icon-facebook"></i></a>
					@endif
					@if(strlen(trim($_contacts->instagram)))
					<a target="_blank" href="{{ $_contacts->instagram }}" class="circled"><i class="xicons icon-instagram-2"></i></a>
					@endif
				</div>
			</div>
		</div>
	</div>
</footer>