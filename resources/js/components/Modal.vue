<template>
    <div class="modal fade" id="modal-default" data-backdrop="false"  aria-hidden="true">
        <div class="modal-dialog" :class="config.size">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><slot name="title"></slot></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <slot></slot>
                </div>
                <div v-if="config.showFooter" class="modal-footer justify-content-between">
                    <button v-if="config.showCloseBtn" type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <slot name="triggers"></slot>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
</template>
<script>
export default {
    name: 'modal-box',
    props: ['config']
}
</script>