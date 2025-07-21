document.addEventListener('DOMContentLoaded', function() {
    const videoItems = document.querySelectorAll('.vgs-video-item');
    videoItems.forEach(item => {
        item.addEventListener('click', function() {
            const videoId = this.getAttribute('data-video-id');
            if (videoId) {
                const wrapper = this.querySelector('.vgs-video-wrapper');
                wrapper.innerHTML = `
                    <iframe 
                        loading="lazy" 
                        src="https://www.youtube.com/embed/${videoId}?autoplay=1" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                `;
            }
        });
    });
});
