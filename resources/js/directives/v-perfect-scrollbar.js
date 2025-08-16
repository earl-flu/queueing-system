import PerfectScrollbar from 'perfect-scrollbar';
import 'perfect-scrollbar/css/perfect-scrollbar.css';

const PerfectScrollbarDirective = {
    mounted(el, binding) {
      const options = binding.value || {};
      el._ps = new PerfectScrollbar(el, options);
    },
    updated(el) {
      el._ps.update();
    },
    unmounted(el) {
      el._ps.destroy();
      delete el._ps;
    },
  };
  
  export default PerfectScrollbarDirective;