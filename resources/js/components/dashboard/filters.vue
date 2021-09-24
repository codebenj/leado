<template>
  <div>
    <el-row class="m-b-lg" :gutter="24">
      <el-col
        :xs="24"
        :sm="24"
        :md="12"
        :lg="8"
        :xl="8"
        class="tablet-show-980"
        v-if="user.user_role.name == 'organisation' && hasCritical"
      >
        <OrgWarning />
      </el-col>

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="4"
        :xl="4"
        v-if="isAssignedRoles(user, ['super_admin', 'administrator', 'user'])"
      >
        <label class="filter-labels"> Filter By Lead Type</label>

        <el-select
          v-model="filters[5].value"
          placeholder="Select Lead Type"
          clearable
          @change="leadTypeChanged"
          :disabled="isLock"
        >
          <el-option
            v-for="(leadType, index) in leadTypes"
            :key="index"
            :value="index"
            :label="index"
            >{{ index }}</el-option
          >
        </el-select>
      </el-col>

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="4"
        :xl="4"
        v-if="
          filters[5].value == '' &&
          isAssignedRoles(user, ['super_admin', 'administrator', 'user']) &&
          tabSelected == 'needs_attention'
        "
      >
        <label class="filter-labels"> Filter By Level </label>

        <el-select
          v-model="filters[0].value"
          placeholder="Select Level"
          clearable
          @change="escalationLevelChanged"
          :disabled="isLock"
        >
          <el-option
            v-for="FL in need_attention_level"
            :key="FL.value"
            :value="FL.value"
            :label="FL.label"
          />
        </el-select>
      </el-col>

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="4"
        :xl="4"
        v-if="
          filters[5].value == '' &&
          isAssignedRoles(user, ['super_admin', 'administrator', 'user']) &&
          tabSelected != 'needs_attention'
        "
      >
        <label class="filter-labels"> Filter By Level </label>

        <el-select
          v-model="filters[0].value"
          placeholder="Select Level"
          clearable
          @change="escalationLevelChanged"
          :disabled="isLock"
        >
          <el-option
            v-for="FL in tempLeadLevel"
            :key="FL.value"
            :value="FL.value"
            :label="FL.label"
          />
        </el-select>
      </el-col>

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="4"
        :xl="4"
        v-if="filters[5].value != '' && tabSelected != 'needs_attention'"
      >
        <label class="filter-labels"> Filter By Level </label>

        <el-select
          v-model="filters[0].value"
          placeholder="Select Level"
          clearable
          @change="escalationLevelChanged"
          :disabled="isLock"
        >
          <el-option
            v-for="FL in tempLeadLevel"
            :key="FL.value"
            :value="FL.value"
            :label="FL.label"
          />
        </el-select>
      </el-col>

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="4"
        :xl="4"
        v-if="filters[5].value != '' && tabSelected == 'needs_attention'"
      >
        <label class="filter-labels"> Filter By Level </label>

        <el-select
          v-model="filters[0].value"
          placeholder="Select Level"
          clearable
          @change="escalationLevelChanged"
          :disabled="isLock"
        >
          <el-option
            v-for="FL in tempLeadLevel"
            :key="FL.value"
            :value="FL.value"
            :label="FL.label"
          />
        </el-select>
      </el-col>

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="4"
        :xl="4"
        v-if="
          filters[0].value != '' &&
            isAssignedRoles(user, ['super_admin', 'administrator', 'user'])
        "
      >
        <label class="filter-labels"> Filter By Status </label>
        <el-select
          v-model="filters[4].value"
          placeholder="Select Status"
          clearable
          :disabled="isLock"
        >
          <span>
            <el-option
              v-for="(status, index) in this.tempLeadType"
              :key="index"
              :value="status"
              :label="status"
              >{{ status }}</el-option
            >
          </span>
        </el-select>
      </el-col>

      <el-col
        class="filter-spacings-at-column-type"
        :class="{ 'width-half': user.user_role.name == 'organisation' }"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="user.user_role.name == 'organisation' ? 3 : 4"
        :xl="user.user_role.name == 'organisation' ? 3 : 4"
        v-if="!isAssignedRoles(user, ['super_admin', 'administrator', 'user'])"
      >
        <label
          class="filter-labels"
          :class="{
            'filter-labels-font': user.user_role.name == 'organisation'
          }"
        >
          Sort By
        </label>
        <el-select v-model="filters[6].value" placeholder="Select Option">
          <span>
            <el-option
              v-for="sorted in sortedBy"
              :key="sorted.value"
              :value="sorted.value"
              :label="sorted.label"
              >{{ sorted.value }}</el-option
            >
          </span>
        </el-select>
      </el-col>

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="user.user_role.name == 'organisation' ? 3 : 4"
        :xl="user.user_role.name == 'organisation' ? 3 : 4"
        v-if="
          filters[0].value == '' ||
            !isAssignedRoles(user, ['super_admin', 'administrator', 'user'])
        "
      >
        <label
          class="filter-labels"
          :class="{
            'filter-labels-font': user.user_role.name == 'organisation',
            'width-half': user.user_role.name == 'organisation'
          }"
        >
          Filter By Status
        </label>
        <el-select
          v-model="filters[4].value"
          placeholder="Select Status"
          clearable
          :disabled="isLock"
        >
          <span>
            <el-option
              v-for="status in lead_statuses"
              :key="status.value"
              :value="status.value"
              :label="status.label"
              >{{ status.value }}</el-option
            >
          </span>
        </el-select>
      </el-col>

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="4"
        :xl="4"
        v-if="isAdmin"
      >
        <label class="filter-labels"> Filter by Organisation </label>
        <el-select
          v-model="filters[1].value"
          filterable
          remote
          clearable
          placeholder="Select or Search"
          :disabled="isLock"
        >
          <el-option
            v-for="organisation in organisations"
            :key="organisation.id"
            :label="organisation.name"
            :value="organisation.id"
          >
            <span>
              {{ organisation.name }}
              <ManualIcon :org="organisation" />
            </span>
            <span v-if="organisation && !organisation.has_postcodes">
              <PostcodeIcon :displayOnly="false" />
            </span>
            <span>
              <MainPriorityIcon :priority="organisation.priority" :tooltip="organisation.priority" :displayOnly="true" />
            </span>
            <span
              v-show="organisation.account_status_type_selection.length > 0"
              class="on-hold"
              >{{ organisation.account_status_type_selection }}</span
            >
          </el-option>
        </el-select>
      </el-col>

      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="user.user_role.name == 'organisation' ? 3 : 4"
        :xl="user.user_role.name == 'organisation' ? 3 : 4"
      >
        <label
          class="filter-labels"
          :class="{
            'filter-labels-font': user.user_role.name == 'organisation'
          }"
        >
          Filter by Pcode or Suburb
        </label>
        <el-input
          v-model="filters[2].value"
          placeholder="Pcode or Suburb"
          clearable
          :disabled="isLock"
        />
      </el-col>
      <el-col
        class="filter-spacings-at-column-type"
        :xs="24"
        :sm="24"
        :md="12"
        :lg="user.user_role.name == 'organisation' ? 3 : 4"
        :xl="user.user_role.name == 'organisation' ? 3 : 4"
      >
        <label class="filter-labels">&nbsp;</label>
        <el-input
          v-model="filters[3].value"
          placeholder="Search..."
          clearable
          :disabled="isLock"
        />
      </el-col>

      <el-col
        :xs="24"
        :sm="24"
        :md="12"
        :lg="10"
        :xl="10"
        class="tablet-hidden-980"
        v-if="user.user_role.name == 'organisation' && hasCritical"
      >
        <OrgWarning />
      </el-col>
    </el-row>
  </div>
</template>
<script>
import { mapGetters } from "vuex";
import { isAssignedRoles } from "~/helpers";
import ManualIcon from "~/components/ManualIcon.vue";
import PostcodeIcon from "~/components/PostcodeIcon.vue";
import MainPriorityIcon from "~/components/priorities/Main.vue";
import { Bus } from "~/app";
import OrgWarning from "~/components/OrgWarning";

export default {
  name: "LeadsFilter",
  props: [
    "filters",
    "filterLevel",
    "leadTypes",
    "leadTypes2",
    "organisations",
    "statuses",
    "filterLevel2",
    "isAdmin",
    "hasCritical",
    "sortedBy",
    "isLock",
    "tabSelected"
  ],
  data() {
    return {
      leadType: "",
      level: "",
      tempLeadType: [],
      tempLeadLevel: [],
      lead_statuses: [],
      need_attention_level:[

        {
          value: "",
          label: "Select"
        },
        {
          value: "Unassigned",
          label: "Unassigned"
        },
        {
          value: "New Lead",
          label: "New Lead"
        },
        {
          value: "Contact",
          label: "Contact"
        },
        {
          value: "In Progress",
          label: "In Progress"
        },
        {
          value: "General Inquiry",
          label: "General Inquiry"
        },
        {
          value: "Supply Only",
          label: "Supply Only"
        },
      ],
      need_attention_lead_types: {
        "Supply & Install": {
          Unassigned: ["Special Opportunity", "Lost"],
          "New Lead": ["Waiting", "Critical" , "Declined"],
          "Contact": [
            "Waiting",
            "Prompt",
            "Critical" ,
            "Discontinued"
          ],
          "In Progress": [
            "Waiting",
            "Prompt",
            "Critical" ,
            "Discontinued"
          ],
        },
        "Supply Only": {
          "Supply Only": ['New', 'In Progress', 'Finalized']
        },
        "General Enquiry": {
          "General Enquiry": ['New', 'In Progress', 'Finalized']
        }
      },
    };
  },
  watch: {
    statuses(newValue, oldValue) {
      this.setStatusData(newValue);
    }
  },
  components: {
    ManualIcon,
    PostcodeIcon,
    OrgWarning,
    MainPriorityIcon
  },
  methods: {
    setStatusData(statuses) {
      if (this.user.user_role.name == "organisation") {
        this.lead_statuses = statuses.filter(res => {
          if (
            !(res.value === "Unassigned" || res.value === "Special Opportunity")
          ) {
            return res;
          }
        });
      } else {
        this.lead_statuses = statuses;
      }
    },

    leadTypeChanged() {
      this.leadType = this.filters[5].value;
      this.filters[0].value = "";
      this.filters[4].value = "";

      try {
        this.tempLeadLevel = this.leadTypes2[this.leadType];
      } catch (e) {
        this.tempLeadLevel = this.filterLevel;
      }
    },

    escalationLevelChanged() {
      this.level = this.filters[0].value;

      try {
        this.tempLeadType = this.leadTypes[this.leadType][this.level];
      } catch (e) {
        this.tempLeadType = this.filterLevel2[this.level];
      }

      this.filters[4].value = "";
    },

    isAssignedRoles: isAssignedRoles,

    checkLeadType() {
      if (this.filters[5].value == "") {
        this.tempLeadLevel = this.filterLevel;
      } else {
        try {
          this.tempLeadLevel = this.leadTypes2[this.filters[5].value];
          this.tempLeadType = this.leadTypes[this.filters[5].value][
            this.filters[0].value
          ];
        } catch (e) {
          this.tempLeadLevel = this.filterLevel;
          this.tempLeadType = this.filterLevel2[this.filters[0].value];
        }
      }
    }
  },

  computed: mapGetters({
    user: "auth/user"
  }),

  mounted() {
    this.setStatusData(this.statuses);
    Bus.$on("saved-query", _ => {
      setTimeout(() => {
        this.checkLeadType();
      }, 3000);
    });
  },

  beforeMount() {
    this.checkLeadType();
  }
};
</script>

<style scoped>
.on-hold{
  float: right; color: #DE1F21; font-size: 11px; padding: 5px; background-color: #FDEEEE; border-radius: 4px; line-height: 15px; margin-top:5px
}
.filter-labels {
  color: grey;
  white-space: nowrap;
}

@media all and (max-width: 768px) {
  .filter-spacings-at-column-type {
    margin-top: 10px;
  }

  .filter-spacings-at-column-type:first-child {
    margin-top: 0px;
  }

  .filter-spacings-at-column-type:last-child {
    margin-top: -10px;
  }
}

.tablet-show-980 {
  padding-bottom: 25px;
}

@media all and (min-width: 981px) {
  .tablet-show-980 {
    display: none !important;
  }

  .tablet-hidden-980 {
    display: block !important;
  }
}

@media all and (max-width: 981px) {
  .tablet-hidden-980 {
    display: none !important;
  }

  .tablet-show-980 {
    display: block !important;
  }
}
</style>
