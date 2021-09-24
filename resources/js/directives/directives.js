import Vue from 'vue';

Vue.directive('dialogDrag', {
    bind(el, binding, vnode, oldVnode) {
        const dialogHeaderEl = el.querySelector('.el-dialog__header');
        const dragDom = el.querySelector('.el-dialog');
        const dragDomParent = dragDom.parentNode;
        dialogHeaderEl.style.cursor = 'move';

        // Get the original attribute
        const sty = dragDom.currentStyle || window.getComputedStyle(dragDom, null);

        // Gets the current value of the drag DOM parent
        let previousDisplayValue = dragDomParent.style.display;
        let initialPosition = dragDom.top;

        //Observer to check if the Dialog has been Closed
        const observer = new MutationObserver((mutations) => {
          mutations.forEach((mutation) => {
            if (mutation.attributeName !== 'style') return;
            let currentValue = mutation.target.style.display;

            //Remove looping
            if (currentValue !== previousDisplayValue) {

              // Sets the position of the Dialog back to the original state
              if (previousDisplayValue !== "none" && currentValue === "none") {
                dragDom.style.left = ``;
                dragDom.style.top = ``;
                document.body.style.position = "relative";
              } else {
                 document.body.style.position = "fixed";
              }

              // Sets the new value of the target display
              previousDisplayValue = mutation.target.style.display;
            }
          });
        });

        observer.observe(dragDomParent, {
          attributes: true //configure it to listen to attribute changes
        });

        dialogHeaderEl.onmousedown = (e) => {
            // Press the mouse to calculate the distance between the current element and the visible area
            const disX = e.clientX - dialogHeaderEl.offsetLeft;
            const disY = e.clientY - dialogHeaderEl.offsetTop;

            // Get the value with px regular matching replacement
            let styL, styT;

            // Note that the value obtained for the first time in ie is the value of px after 50% movement of the component
            if(sty.left.includes('%')) {
                styL = +document.body.clientWidth * (+sty.left.replace(/\%/g, '') / 100);
                styT = +document.body.clientHeight * (+sty.top.replace(/\%/g, '') / 100);
            }else {
                styL = +sty.left.replace(/\px/g, '');
                styT = +sty.top.replace(/\px/g, '');
            };

            document.onmousemove = function (e) {
                // Calculate the moving distance through event delegation
                const l = e.clientX - disX;
                const t = e.clientY - disY;

                //Pass out the position at this time
                //binding.value({x:e.pageX,y:e.pageY})

                // Move current element
                dragDom.style.left = `${l + styL}px`;
                dragDom.style.top = `${t + styT}px`;
            };

            document.onmouseup = function (e) {
                document.onmousemove = null;
                document.onmouseup = null;
            };
        }
    }
})
