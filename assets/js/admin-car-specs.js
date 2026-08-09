document.addEventListener("DOMContentLoaded", function () {
	var box = document.querySelector(".afq-specs");
	if (!box) {
		return;
	}

	var tabs   = box.querySelectorAll(".afq-specs__tab");
	var panels = box.querySelectorAll(".afq-specs__panel");

	tabs.forEach(function (tab) {
		tab.addEventListener("click", function () {
			var target = tab.getAttribute("data-afq-tab");

			tabs.forEach(function (t) {
				t.classList.toggle("is-active", t === tab);
			});
			panels.forEach(function (p) {
				p.classList.toggle("is-active", p.getAttribute("data-afq-panel") === target);
			});
		});
	});
});
