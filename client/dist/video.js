(function(n){var c,o,a,f;function v(){d()}function d(){const e=r();if(e){const t=e.getValue();t.host&&t.id?(o.html('<a href="'+m(t)+'" class="ss-ui-dialog-link"><img src="'+h(t)+'" alt="Preview"></a>'),o.addClass("goldfinchvideo__preview--display")):o.removeClass("goldfinchvideo__preview--display")}}function h(e){let t;return e.host=="youtube"?t="https://img.youtube.com/vi/"+e.id+"/sddefault.jpg":e.host=="vimeo"&&(t="https://vumbnail.com/"+e.id+"_large.jpg"),t}function m(e){let t;return e.host=="youtube"?t="https://www.youtube.com/embed/"+e.id:e.host=="vimeo"&&(t="https://player.vimeo.com/video/"+e.id),t}function g(e){var t,i;if(e.search("youtube.com/")>=0||e.search("youtu.be/")>=0?(t="youtube",i=p(e)):e.search("vimeo.com/")>=0&&(t="vimeo",i=w(e)),t&&i){const u=r();if(u){var s=u.getValue();s.host=t,s.id=i,u.setValue(s),d()}}}function p(e){return e=e.split(/(vi\/|v%3D|v=|\/v\/|youtu\.be\/|\/embed\/)/),e[2]!==void 0?e[2].split(/[^0-9a-z_\-]/i)[0]:e[0]}function w(e){let t=/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/?(showcase\/)*([0-9))([a-z]*\/)*([0-9]{6,11})[?]?.*/,i=e.match(t);if(i&&i.length==7)return i[6]}function l(e){v()}function r(){return window.jsoneditor?window.jsoneditor[c]:null}n(document).ready(()=>{let e=0;var t=setInterval(()=>{e++,r()&&(clearInterval(t),l()),e>5&&clearInterval(t)},500)}),n(".cms-edit-form").entwine({onmatch(e){this._super(e)},onunmatch(e){this._super(e)},onaftersubmitform(e,t){o=n(this).find("[data-goldfinch-video-preview]"),l(r()),d()}}),n.entwine("ss",e=>{e("[data-goldfinch-video-field]").entwine({onmatch(){f=e(this).find("[data-goldfinch-video-link-detector]"),o=e(this).find("[data-goldfinch-video-preview]"),a=e(this).find(".json-editor").next(),c=a.attr("id"),f.on("keyup",i=>{g(i.target.value)})}})})})(jQuery);
