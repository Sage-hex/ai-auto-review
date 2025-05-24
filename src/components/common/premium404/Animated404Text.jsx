import { useRef, useEffect } from 'react';
import { motion, useMotionValue, useTransform } from 'framer-motion';

const Animated404Text = ({ mousePosition }) => {
  const containerRef = useRef(null);
  const x = useMotionValue(0);
  const y = useMotionValue(0);
  
  // Transform mouse movement to rotation
  const rotateX = useTransform(y, [-300, 300], [10, -10]);
  const rotateY = useTransform(x, [-300, 300], [-10, 10]);
  
  // Update motion values based on mouse position
  useEffect(() => {
    if (!containerRef.current || !mousePosition.x || !mousePosition.y) return;
    
    const rect = containerRef.current.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;
    
    // Calculate distance from center
    const distanceX = mousePosition.x - centerX;
    const distanceY = mousePosition.y - centerY;
    
    // Update motion values
    x.set(distanceX);
    y.set(distanceY);
  }, [mousePosition, x, y]);
  
  return (
    <div 
      ref={containerRef}
      className="relative perspective-[1000px] w-full max-w-md mx-auto"
    >
      <motion.div
        className="relative"
        style={{
          rotateX,
          rotateY,
          transformStyle: 'preserve-3d',
        }}
      >
        {/* Main 404 text */}
        <h1 className="text-[150px] font-black text-center leading-none tracking-tighter">
          <span className="bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-purple-600">
            404
          </span>
        </h1>
        
        {/* Shadow layer */}
        <div 
          className="absolute inset-0 text-[150px] font-black text-center leading-none tracking-tighter opacity-20 blur-md"
          style={{ transform: 'translateZ(-20px)' }}
        >
          404
        </div>
        
        {/* Highlight layer */}
        <div 
          className="absolute inset-0 text-[150px] font-black text-center leading-none tracking-tighter text-white opacity-10"
          style={{ transform: 'translateZ(10px)' }}
        >
          404
        </div>
        
        {/* Glitch effect */}
        <div className="absolute inset-0 overflow-hidden opacity-10">
          <div className="glitch-effect text-[150px] font-black text-center leading-none tracking-tighter text-red-500">
            404
          </div>
        </div>
      </motion.div>
    </div>
  );
};

export default Animated404Text;
