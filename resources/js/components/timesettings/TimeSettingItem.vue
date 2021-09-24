<template>
    <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
        <el-card class="timesetting m-b-md" shadow="never">
            <div class="timesetting-header">
                <p>
                    <strong>{{ timeSetting.name }}</strong>

                    <span class="timesetting-edit clickable fl-right" @click="deleteSettings">
                        <i class="el-icon-delete"></i>
                    </span>
                    <span class="timesetting-edit clickable fl-right" @click="editSettings">
                        <i class="el-icon-edit"></i>
                    </span>
                </p>
            </div>    
            <div class="timesetting-body d-flex">
                <table v-if="timeSetting.type == 'recurring'">
                    <tr>
                        <td class="start">PAUSE ON</td>
                        <td class="stop">RESUME ON</td>
                    </tr>
                    <tr>
                        <td>Day: {{ timeSetting.start_day }}</td>
                        <td>Day: {{ timeSetting.stop_day }}</td>
                    </tr>
                </table>

                <table v-else>
                    <tr>
                        <td class="start">PAUSE ON</td>
                        <td class="stop">RESUME ON</td>
                    </tr>
                    <tr>
                        <td>Date: {{ timeSetting.start_date }}</td>
                        <td>Date: {{ timeSetting.stop_date }}</td>
                    </tr>
                    <tr>
                        <td>Time: {{ timeSetting.start_time }}</td>
                        <td>Time: {{ timeSetting.stop_time }}</td>
                    </tr>
                </table>

                <div class="labels">
                    <el-tag class="clickable" 
                        @click="changeStatus"
                        :type="timeSetting.is_active ? 'success' : 'info'" 
                        effect="dark">{{ timeSetting.is_active ? 'Active' : 'Inactive' }}</el-tag>
                    <el-tag :type="typeColor" effect="dark" class="text-capitalize">{{ timeSetting.type }}</el-tag>
                </div>
            </div>
        </el-card>
    </el-col>
</template>

<script>
import Swal from "sweetalert2";

export default {
    name: "TimeSettingItem",

    props: {
        timeSetting: { type: Object, default: null },
    },

    data: () => ({
        timesettings: null,
    }),

    computed: {
        typeColor() {
            return this.timeSetting.type == 'recurring' ? 'primary' : 'danger'
        }
    },
    
    methods: {
        editSettings() {
            this.$store.dispatch("timesetting/editTimesetting", this.timeSetting);
        },

        deleteSettings() {
            this.$store.dispatch("timesetting/deleteTimesetting", this.timeSetting.id)
            .then(({ success, message, errors }) => {
                    if ( success ) {
                        Swal.fire({
                            title: 'Success!',
                            text: message,
                            type: 'success',
                        })
                        this.$store.dispatch("timesetting/fetchTimesettings")
                        .then(({ data }) => {
                            this.timesettings = data.data;
                            console.log(data.data);
                        });
                    }
                });
        },

        changeStatus() {
            this.$store.dispatch('timesetting/changeTimesettingStatus', this.timeSetting.id)
                .then(({ success, message, errors }) => {
                    if ( success ) {
                        this.$store.dispatch('timesetting/setDialog', false)

                        Swal.fire({
                            title: 'Success!',
                            text: message,
                            type: 'success',
                        })
                    }
                })
        },
    }
};
</script>
